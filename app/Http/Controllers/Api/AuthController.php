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
use App\Rules\ZxcvbnPassword;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        Log::info('[AuthController@register] Menerima permintaan registrasi API baru.');

        try {
            $existingUnverifiedUser = User::where('email', $request->email)->whereNull('email_verified_at')->first();
            if ($existingUnverifiedUser) {
                Log::info('Menghapus pengguna lama yang belum terverifikasi untuk registrasi ulang.', ['email' => $request->email]);
                $existingUnverifiedUser->delete();
            }

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => [
                    'required',
                    'confirmed',
                    PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                    new ZxcvbnPassword(2)
                ]
            ]);

            $otpCode = rand(100000, 999999);
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'user',
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            Log::info('[AuthController@register] SUKSES: User dibuat, mengirim OTP.', ['user_id' => $user->user_id]);
            Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.registration_otp'));

            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi berhasil! Cek email Anda untuk kode OTP.',
                'data' => [
                    'email' => $user->email
                ]
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('[AuthController@register] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[AuthController@register] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
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

            // Cek 1: User ada dan password benar
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                Log::warning('[AuthController@login] GAGAL: Email atau password salah.', ['email' => $email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password salah.'
                ], 401);
            }

            // Cek 2: Akun belum diverifikasi (hanya untuk non-admin)
            if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                Log::warning('[AuthController@login] GAGAL: Akun belum diverifikasi.', ['email' => $email]);
                
                // Daripada mengarahkan ke verifikasi, kembalikan respons 403 (Forbidden)
                // dan berikan kode khusus agar Flutter bisa mengarahkan ke VerifyOtpScreen.
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda belum diverifikasi. Silakan verifikasi akun Anda.',
                    'code' => 'UNVERIFIED', 
                    'data' => [
                        'email' => $user->email // Kirim email agar Flutter bisa navigasi ke VerifyOtpScreen
                    ]
                ], 403);
            }
            
            // Cek 3: Akun dikunci
            if ($user->locked_until && now()->lt($user->locked_until)) {
                $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
                return response()->json([
                    'status' => 'error',
                    'message' => "Akun Anda dikunci. Coba lagi dalam {$minutes} menit."
                ], 403);
            }
            
            // --- Logika Token Sanctum (Pengganti Sesi Web) ---
            
            // Hapus token lama dan buat token baru
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
            // Laravel akan secara otomatis menangani ValidationException dengan respons 422 JSON jika ini adalah rute API
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