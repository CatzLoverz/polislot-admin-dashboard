<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
{
    Log::info('[AuthController@register] Menerima permintaan registrasi API baru.');

    try {
        // Hapus user lama yang belum verifikasi
        $existingUnverifiedUser = User::where('email', $request->email)
            ->whereNull('email_verified_at')
            ->first();

        if ($existingUnverifiedUser) {
            Log::info('Menghapus pengguna lama yang belum terverifikasi untuk registrasi ulang.', [
                'email' => $request->email
            ]);
            $existingUnverifiedUser->delete();
        }

        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ]
        ]);

        // Buat user baru + kirim OTP
        $otpCode = rand(100000, 999999);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'user',
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Log::info('[AuthController@register] SUKSES: User dibuat, mengirim OTP.', [
            'user_id' => $user->user_id
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.registration_otp'));

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil! Cek email Anda untuk kode OTP.',
            'data' => [
                'email' => $user->email
            ]
        ], 201);

    } catch (ValidationException $e) {
        Log::warning('[AuthController@register] GAGAL: Validasi gagal.', [
            'errors' => $e->errors()
        ]);

        // ✅ Ambil hanya 1 pesan error pertama
        $firstError = collect($e->errors())->flatten()->first();

        return response()->json([
            'status' => 'error',
            'message' => $firstError ?? 'Validasi gagal. Periksa input Anda.'
        ], 422);

    } catch (\Exception $e) {
        Log::error('[AuthController@register] GAGAL: Error sistem.', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server.'
        ], 500);
    }
}

    public function verifyRegisterOtp(Request $request): JsonResponse
    {
        Log::info('[AuthController@verifyRegisterOtp] Menerima permintaan verifikasi OTP via API.');

        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email|exists:users,email',
                'otp' => 'required|numeric|digits:6'
            ]);

            $user = User::where('email', $validatedData['email'])->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email ini sudah terverifikasi.'
                ], 400);
            }

            if ($user->otp_code !== $validatedData['otp'] || Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode OTP salah atau telah kedaluwarsa.'
                ], 422);
            }

            $user->email_verified_at = now();
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('[AuthController@verifyRegisterOtp] SUKSES: Verifikasi OTP berhasil.', ['user_id' => $user->user_id]);
            return response()->json([
                'status' => 'success',
                'message' => 'Verifikasi berhasil! Selamat datang.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('[AuthController@verifyRegisterOtp] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[AuthController@verifyRegisterOtp] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    /**
     * Mengirim ulang kode OTP registrasi via API.
     * Endpoint ini mengharapkan 'email' di body request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendRegisterOtp(Request $request): JsonResponse
    {
        Log::info('[AuthController@resendRegisterOtp] Menerima permintaan kirim ulang OTP registrasi via API.');

        try {
            // 1. Validasi input email
            $validatedData = $request->validate([
                'email' => 'required|string|email|exists:users,email',
            ]);
            $email = $validatedData['email'];

            // 2. Ambil user
            $user = User::where('email', $email)->first();

            // 3. Cek apakah sudah terverifikasi
            if ($user->hasVerifiedEmail()) {
                Log::warning('[AuthController@resendRegisterOtp] GAGAL: Email sudah terverifikasi.', ['email' => $email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terverifikasi, tidak perlu mengirim ulang OTP.'
                ], 400);
            }

            // 4. Generate dan simpan OTP baru
            $newOtpCode = rand(100000, 999999);
            $user->otp_code = $newOtpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            // 5. Kirim email
            Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'emails.registration_otp'));
            Log::info('[AuthController@resendRegisterOtp] SUKSES: OTP registrasi baru dikirim via API.', ['user_id' => $user->user_id]);

            // 6. Respons sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Kode OTP baru telah dikirim ke email Anda. Cek kembali dalam 10 menit ke depan.',
                'data' => [
                    'email' => $user->email
                ]
            ], 200);

        } catch (ValidationException $e) {
            // Laravel secara otomatis melempar respons 422 untuk validasi.
            Log::warning('[AuthController@resendRegisterOtp] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            // Error sistem tak terduga
            Log::error('[AuthController@resendRegisterOtp] GAGAL: Error sistem.', ['email' => $request->email, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim ulang OTP karena kesalahan server.'
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse 
{
    $email = $request->input('email');
    Log::info("[AuthController@login] Menerima percobaan login (API) untuk email: {$email}");

    try {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $email)->first();

        // 🔹 Cek 1: Jika email tidak ditemukan
        if (!$user) {
            Log::warning('[AuthController@login] GAGAL: Email tidak ditemukan.', ['email' => $email]);
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak terdaftar. Silakan periksa kembali atau daftar akun baru.'
            ], 404);
        }

        // 🔹 Cek 2: Akun dikunci
        if ($user->locked_until && now()->lt($user->locked_until)) {
            $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
            return response()->json([
                'status' => 'error',
                'message' => "Akun Anda dikunci. Coba lagi dalam {$minutes} menit."
            ], 403);
        }

        // 🔹 Cek 3: Password salah
        if (!Hash::check($credentials['password'], $user->password)) {
            $user->increment('failed_attempts');

            $maxAttempts = 4;
            $remaining = $maxAttempts - $user->failed_attempts;

            // Jika sudah mencapai batas -> kunci akun
            if ($user->failed_attempts >= $maxAttempts) {
                $user->update([
                    'locked_until' => now()->addMinutes(10),
                    'failed_attempts' => 0
                ]);

                Log::warning('[AuthController@login] GAGAL: Akun dikunci setelah 4x gagal login.', ['email' => $email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda telah dikunci selama 10 menit karena 4 kali percobaan login gagal.'
                ], 403);
            }

            Log::warning('[AuthController@login] GAGAL: Password salah.', [
                'email' => $email,
                'failed_attempts' => $user->failed_attempts
            ]);

            return response()->json([
                'status' => 'error',
                'message' => "Email atau password salah. Tersisa {$remaining} percobaan lagi sebelum akun dikunci."
            ], 401);
        }

        // 🔹 Cek 4: Akun belum diverifikasi
        if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
            Log::warning('[AuthController@login] GAGAL: Akun belum diverifikasi.', ['email' => $email]);
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum diverifikasi. Silakan verifikasi akun Anda.',
                'code' => 'UNVERIFIED',
                'data' => [
                    'email' => $user->email
                ]
            ], 403);
        }

        // 🔹 Login berhasil
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Reset percobaan gagal
        $user->update(['failed_attempts' => 0, 'locked_until' => null]);

        Log::info('[AuthController@login] SUKSES (API).', [
            'user_id' => $user->user_id,
            'role' => $user->role
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ], 200);

    } catch (ValidationException $e) {
        Log::warning('[AuthController@login] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
        throw $e;
    } catch (\Exception $e) {
        Log::error('[AuthController@login] ERROR SISTEM.', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server.'
        ], 500);
    }
}

    // --- LUPA & RESET PASSWORD ---

public function sendResetOtp(Request $request): JsonResponse
{
    Log::info('[AuthController@sendResetOtp] Permintaan reset password (API).');

    try {
        $validated = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $validated['email'])->first();

        $otpCode = rand(100000, 999999);
        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.reset_password_otp'));
        Log::info('[AuthController@sendResetOtp] OTP reset dikirim.', ['email' => $user->email]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda.',
            'data' => [
                'email' => $user->email,
                'expires_in' => 600 // 10 menit dalam detik
            ]
        ], 200);
    } catch (ValidationException $e) {
        Log::warning('[AuthController@sendResetOtp] Validasi gagal.', ['errors' => $e->errors()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Email tidak valid atau belum terdaftar.'
        ], 422);
    } catch (\Exception $e) {
        Log::error('[AuthController@sendResetOtp] Gagal sistem.', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim OTP. Coba lagi nanti.'
        ], 500);
    }
}

public function resendResetOtp(Request $request): JsonResponse
{
    try {
        $validated = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $validated['email'])->first();

        $otpCode = rand(100000, 999999);
        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.reset_password_otp'));
        Log::info('[AuthController@resendResetOtp] OTP baru dikirim.', ['email' => $user->email]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP baru telah dikirim ke email Anda.',
            'data' => [
                'email' => $user->email,
                'expires_in' => 600
            ]
        ], 200);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email tidak valid atau belum terdaftar.'
        ], 422);
    } catch (\Exception $e) {
        Log::error('[AuthController@resendResetOtp] Gagal sistem.', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim ulang OTP.'
        ], 500);
    }
}

public function verifyResetOtp(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|numeric|digits:6'
    ]);

    try {
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            Log::warning('[AuthController@verifyResetOtp] OTP salah.', ['email' => $request->email]);
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP salah.'
            ], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            Log::warning('[AuthController@verifyResetOtp] OTP kedaluwarsa.', ['email' => $request->email]);
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP telah kedaluwarsa.'
            ], 400);
        }

        Log::info('[AuthController@verifyResetOtp] OTP valid.', ['email' => $request->email]);
        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil diverifikasi. Silakan lanjut reset password.',
            'data' => [
                'email' => $user->email
            ]
        ], 200);
    } catch (\Exception $e) {
        Log::error('[AuthController@verifyResetOtp] Gagal sistem.', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem.'
        ], 500);
    }
}

    public function resetPassword(Request $request): JsonResponse
{
    try {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ]
        ]);

        $user = User::where('email', $request->email)->first();

        // 🔴 Pastikan user dan OTP masih valid
        if (!$user || !$user->otp_code || now()->gt($user->otp_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi OTP tidak valid atau telah kedaluwarsa.'
            ], 400);
        }

        // 🧩 Tambahkan validasi: Password baru tidak boleh sama dengan password lama
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password baru tidak boleh sama dengan password sebelumnya.'
            ], 400);
        }

        // 🔵 Update password baru
        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        Log::info('[AuthController@resetPassword] Password berhasil direset.', ['email' => $user->email]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset. Silakan login kembali.'
        ], 200);

    } catch (ValidationException $e) {
        $error = collect($e->errors())->flatten()->first();
        return response()->json([
            'status' => 'error',
            'message' => $error ?? 'Input tidak valid.'
        ], 422);
    } catch (\Exception $e) {
        Log::error('[AuthController@resetPassword] Gagal sistem.', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server.'
        ], 500);
    }
}

    public function logout(Request $request): JsonResponse // Ubah tipe kembalian menjadi JsonResponse
    {
        // Pengguna harus sudah diotentikasi oleh Sanctum (middleware 'auth:sanctum')
        // Dapatkan pengguna dari token saat ini
        $user = $request->user();
        
        if ($user) {
            Log::info('[AuthController@logout] Memulai proses logout (API).', ['user_id' => $user->user_id]);
            // Hapus token yang digunakan saat ini
            $user->currentAccessToken()->delete();
            Log::info('[AuthController@logout] SUKSES: Token dicabut.', ['user_id' => $user->user_id]);

            // Ganti sesi/redirect dengan respons JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil logout.'
            ], 200);
        }
        
        // Respons jika token tidak valid/tidak ada (meskipun seharusnya dihandle middleware)
        return response()->json([
            'status' => 'error',
            'message' => 'Pengguna tidak terotentikasi.'
        ], 401);
    }
}