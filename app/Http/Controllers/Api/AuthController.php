<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    // =========================================================================
    // 🟢 REGISTRASI & VERIFIKASI OTP
    // =========================================================================

    /**
     * Memproses registrasi pengguna baru.
     */
    public function register(Request $request): JsonResponse
    {
        Log::info('[AuthController@register] Permintaan registrasi baru.');

        try {
            return DB::transaction(function () use ($request) {
                $existingUnverifiedUser = User::where('email', $request->email)
                    ->whereNull('email_verified_at')
                    ->first();

                if ($existingUnverifiedUser) {
                    $existingUnverifiedUser->delete();
                }

                $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users,email',
                    'password' => [
                        'required',
                        'confirmed',
                        PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
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

                Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.registration_otp', 'Kode Verifikasi Akun Anda'));

                Log::info('[AuthController@register] SUKSES.', ['user_id' => $user->user_id]);

                return $this->sendSuccess(
                    'Registrasi berhasil! Cek email Anda untuk kode OTP.',
                    ['email' => $user->email],
                    201
                );
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[AuthController@register] ERROR.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Memverifikasi OTP Registrasi.
     */
    public function registerOtpVerify(Request $request): JsonResponse
    {
        Log::info('[AuthController@registerOtpVerify] Menerima permintaan verifikasi OTP.');

        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'email' => 'required|email|exists:users,email',
                    'otp' => 'required|numeric|digits:6'
                ]);

                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                if ($user->hasVerifiedEmail()) {
                    return $this->sendError('Email ini sudah terverifikasi.', 400);
                }

                if ($user->otp_code != $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                    return $this->sendError('Kode OTP salah atau telah kedaluwarsa.', 422);
                }

                $user->email_verified_at = now();
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->save();

                $token = $user->createToken('auth_token')->plainTextToken;

                Log::info('[AuthController@registerOtpVerify] SUKSES: Verifikasi OTP berhasil.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Verifikasi berhasil! Selamat datang.', [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $this->formatUser($user)
                ]);
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[VerifyOtp] Error.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan sistem.', 500);
        }
    }

    /**
     * Mengirim ulang OTP Registrasi.
     */
    public function registerOtpResend(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate(['email' => 'required|email|exists:users,email']);
                
                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                if ($user->hasVerifiedEmail()) {
                    return $this->sendError('Email sudah terverifikasi.', 400);
                }

                $newOtpCode = rand(100000, 999999);
                $user->update([
                    'otp_code' => $newOtpCode,
                    'otp_expires_at' => Carbon::now()->addMinutes(10)
                ]);

                Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'emails.registration_otp', 'Kode Verifikasi Akun Anda'));
                
                Log::info('[AuthController@registerOtpResend] SUKSES: OTP registrasi baru dikirim.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Kode OTP baru telah dikirim.', ['email' => $user->email]);
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[AuthController@registerOtpResend] GAGAL.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal mengirim ulang OTP.', 500);
        }
    }

    // =========================================================================
    // 🔵 LOGIN & LOGOUT
    // =========================================================================

    /**
     * Memproses Login.
     */
    public function login(Request $request): JsonResponse
    {
        $email = $request->input('email');
        Log::info("[AuthController@login] Menerima percobaan login untuk email: {$email}");

        try {
            return DB::transaction(function () use ($request, $email) {
                $credentials = $request->validate([
                    'email' => 'required|string|email',
                    'password' => 'required|string'
                ]);

                $user = User::where('email', $email)->lockForUpdate()->first();

                if (!$user) {
                    return $this->sendError('Email tidak ditemukan.', 404);
                }

                if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                    Log::warning('[AuthController@login] GAGAL: Akun belum diverifikasi.', ['email' => $email]);
                    return $this->sendError('Akun Anda belum diverifikasi.', 403, ['email' => $user->email], 'UNVERIFIED');
                }

                if ($user->locked_until && now()->lt($user->locked_until)) {
                    $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
                    return $this->sendError("Akun Anda dikunci. Coba lagi dalam {$minutes} menit.", 403);
                }

                if (!Hash::check($credentials['password'], $user->password)) {
                    $user->increment('failed_attempts');
                    Log::warning('[AuthController@login] GAGAL: Password salah.', ['attempts' => $user->failed_attempts]);

                    if ($user->failed_attempts >= 3) {
                        $lockMinutes = 15;
                        $user->update(['locked_until' => now()->addMinutes($lockMinutes), 'failed_attempts' => 0]);
                        return $this->sendError("Akun Anda dikunci selama {$lockMinutes} menit.", 403);
                    }

                    $sisa = 3 - $user->failed_attempts;
                    return $this->sendError("Password salah. Sisa percobaan: {$sisa} kali.", 401);
                }

                $user->tokens()->delete(); 
                $token = $user->createToken('auth_token')->plainTextToken;
                
                $user->update(['failed_attempts' => 0, 'locked_until' => null]);

                Log::info('[AuthController@login] SUKSES.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Login berhasil!', [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $this->formatUser($user)
                ]);
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[AuthController@login] ERROR SISTEM.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Memproses Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Log::info('[AuthController@logout] Memulai proses logout.', ['user_id' => $user->user_id]);
            $user->currentAccessToken()->delete();
            return $this->sendSuccess('Berhasil logout.');
        }
        return $this->sendError('Token tidak valid.', 401);
    }

    // =========================================================================
    // 🟡 LUPA & RESET PASSWORD
    // =========================================================================

    /**
     * Memproses permintaan email untuk reset password (mengirim OTP).
     */
    public function forgotPasswordVerify(Request $request): JsonResponse
    {
        Log::info('[AuthController@forgotPasswordVerify] Menerima permintaan reset password.');

        try {
            return DB::transaction(function () use ($request) {
                $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);
                $user = User::where('email', $validatedData['email'])->lockForUpdate()->first();

                $otpCode = rand(100000, 999999);
                $user->otp_code = $otpCode;
                $user->otp_expires_at = Carbon::now()->addMinutes(10);
                $user->save();

                Log::info('Mengirim OTP reset password.', ['user_id' => $user->user_id]);
                Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.reset_password_otp', 'Kode Reset Password'));

                return $this->sendSuccess('Kode OTP telah dikirim ke email Anda.', ['email' => $user->email]);
            });

        } catch (ValidationException $e) {
             return $this->sendError('Email tidak ditemukan.', 422);
        } catch (\Exception $e) {
            Log::error('[AuthController@sendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal mengirim OTP.', 500);
        }
    }

    /**
     * Memverifikasi OTP untuk reset password.
     */
    public function forgotPasswordOtpVerify(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email', 'otp' => 'required']);
            $user = User::where('email', $request->email)->firstOrFail();

            if ($user->otp_code != $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                return $this->sendError('Kode OTP salah atau telah kedaluwarsa.', 400);
            }

            Log::info('[AuthController@verifyResetOtp] OTP valid, lanjut ke reset password.', ['email' => $request->email]);

            return $this->sendSuccess('OTP valid. Silakan reset password.', ['email' => $user->email]);

        } catch (\Exception $e) {
            Log::error('[AuthController@verifyResetOtp] Gagal verifikasi.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan sistem.', 500);
        }
    }

    /**
     * Mengirim ulang OTP untuk reset password.
     */
    public function forgotPasswordOtpResend(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate(['email' => 'required|email|exists:users,email']);
                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                $newOtpCode = rand(100000, 999999);
                $user->update([
                    'otp_code' => $newOtpCode,
                    'otp_expires_at' => Carbon::now()->addMinutes(10)
                ]);

                Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'emails.reset_password_otp', 'Kode Reset Password'));
                
                Log::info('[AuthController@resendResetOtp] SUKSES: OTP reset baru dikirim.', ['email' => $request->email]);

                return $this->sendSuccess('Kode OTP baru telah dikirim ke email Anda.', ['email' => $user->email]);
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[AuthController@resendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal mengirim ulang OTP.', 500);
        }
    }

    /**
     * Memproses penyimpanan password baru.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'email' => 'required|email|exists:users,email',
                    'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()]
                ]);

                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                if (Hash::check($request->password, $user->password)) {
                     return $this->sendError('Password baru tidak boleh sama dengan yang lama.', 400);
                }

                $user->password = Hash::make($request->password);
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->failed_attempts = 0;
                $user->locked_until = null; 
                $user->save();

                Log::info('[AuthController@resetPassword] SUKSES: Password direset.', ['email' => $request->email]);

                return $this->sendSuccess('Password berhasil direset. Silakan login.');
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[AuthController@resetPassword] Gagal sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal mereset password.', 500);
        }
    }

    // =========================================================================
    // 🛠️ HELPER FUNCTIONS
    // =========================================================================

    private function sendSuccess($message, $data = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function sendError($message, $code = 400, $data = null, $errorCode = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ];

        if ($errorCode) {
            $response['code'] = $errorCode;
        }

        return response()->json($response, $code);
    }

    private function sendValidationError(ValidationException $e)
    {
        $firstError = collect($e->errors())->flatten()->first();
        return response()->json([
            'status' => 'error',
            'message' => $firstError,
            'data' => null,
            'errors' => $e->errors()
        ], 422);
    }

    private function formatUser($user)
    {
        return [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
        ];
    }
}