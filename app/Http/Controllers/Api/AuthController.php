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
}
