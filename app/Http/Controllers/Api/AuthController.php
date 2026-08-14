<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoginNotificationMail;
use App\Mail\SendOtpMail;
use App\Models\User;
use App\Services\MissionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected $missionService;

    /**
     * Konstruktor.
     *
     * @param MissionService $missionService Service misi
     */
    public function __construct(MissionService $missionService)
    {
        $this->missionService = $missionService;
    }

    /**
     * Endpoint untuk mendapatkan data user saat ini (pengganti route /user).
     * Sekaligus mentrigger misi login harian.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function authCheck(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // === LOGIC MISI LOGIN ===
        // Key unik: user_id + tanggal hari ini (Y-m-d)
        $cacheKey = 'daily_login_'.$user->user_id.'_'.now()->format('Y-m-d');

        // Cek apakah user sudah tercatat login hari ini?
        if (! Cache::has($cacheKey)) {

            // Jika BELUM, catat progress misi
            try {
                $this->missionService->updateProgress($user->user_id, 'LOGIN_ACTION');
                // Simpan penanda di cache sampai akhir hari
                Cache::put($cacheKey, true, now()->endOfDay());
            } catch (Exception $e) {
                Log::error('Gagal update misi: '.$e->getMessage());
            }
        }

        $userData = $this->formatUser($user);

        $userData['email_verified_at'] = $user->email_verified_at;
        $userData['created_at'] = $user->created_at;
        $userData['updated_at'] = $user->updated_at;

        Log::info('Akun masuk ke Aplikasi', ['user_id' => $user->user_id ?? null]);

        return $this->sendSuccess('Data profil berhasil diambil.', $userData);
    }

    // =========================================================================
    // 🟢 REGISTRASI & VERIFIKASI OTP
    // =========================================================================

    /**
     * Memproses registrasi pengguna baru.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => [
                    'required',
                    'confirmed',
                    PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                ],
            ]);

            // Safety net: hapus baris unverified warisan flow lama (jika ada)
            User::where('email', $validatedData['email'])
                ->whereNull('email_verified_at')
                ->delete();

            $emailLower = Str::lower($validatedData['email']);
            $otpCode = random_int(100000, 999999);

            Cache::put(
                "reg_pending_{$emailLower}",
                [
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'otp_hash' => hash('sha256', (string) $otpCode),
                ],
                now()->addMinutes(10)
            );

            Mail::to($validatedData['email'])->send(
                new SendOtpMail($otpCode, 'Emails.registration_otp', 'Kode Verifikasi Akun Anda')
            );

            Log::info('Registrasi berhasil (pending cache).');

            return $this->sendSuccess(
                'Registrasi berhasil! Cek email Anda untuk kode OTP.',
                ['email' => $validatedData['email']],
                201
            );

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Memverifikasi OTP Registrasi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerOtpVerify(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|numeric|digits:6',
            ]);

            $emailLower = Str::lower($request->email);
            $cacheKey = "reg_pending_{$emailLower}";

            $payload = Cache::get($cacheKey);

            if (!$payload) {
                if (User::where('email', $request->email)->whereNotNull('email_verified_at')->exists()) {
                    Log::warning('Email sudah terverifikasi.');
                    return $this->sendError('Email ini sudah terverifikasi.', 400);
                }

                Log::warning('Payload cache registrasi tidak ditemukan atau expired.');
                return $this->sendError('Kode OTP salah atau telah kedaluwarsa.', 422);
            }

            if (hash('sha256', (string) $request->otp) !== $payload['otp_hash']) {
                Log::warning('Kode OTP registrasi salah.');
                return $this->sendError('Kode OTP salah atau telah kedaluwarsa.', 422);
            }

            $claimed = Cache::pull($cacheKey);
            if (!$claimed) {
                Log::warning('Race condition: cache sudah di-pull request lain.');
                return $this->sendError('Verifikasi sedang diproses atau sudah selesai.', 409);
            }

            $user = new User([
                'name' => $claimed['name'],
                'email' => $claimed['email'],
                'password' => $claimed['password'],
                'role' => 'user',
            ]);
            $user->email_verified_at = now();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Verifikasi OTP berhasil, user terdaftar.', ['user_id' => $user->user_id]);

            return $this->sendSuccess('Verifikasi berhasil! Selamat datang.', [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUser($user),
            ]);

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Terjadi kesalahan sistem.', 500);
        }
    }

    /**
     * Mengirim ulang OTP Registrasi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerOtpResend(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email']);

            $emailLower = Str::lower($request->email);
            $cacheKey = "reg_pending_{$emailLower}";

            if (User::where('email', $request->email)->whereNotNull('email_verified_at')->exists()) {
                Log::warning('Email sudah terverifikasi.');

                return $this->sendError('Email sudah terverifikasi.', 400);
            }

            $payload = Cache::get($cacheKey);
            if (!$payload) {
                Log::warning('Sesi registrasi tidak ditemukan.');

                return $this->sendError('Sesi registrasi tidak ditemukan, silakan daftar ulang.', 422);
            }

            $newOtpCode = random_int(100000, 999999);
            $payload['otp_hash'] = hash('sha256', (string) $newOtpCode);

            Cache::put($cacheKey, $payload, now()->addMinutes(10));

            Mail::to($payload['email'])->send(
                new SendOtpMail($newOtpCode, 'Emails.registration_otp', 'Kode Verifikasi Akun Anda')
            );

            Log::info('OTP registrasi baru dikirim.');

            return $this->sendSuccess('Kode OTP baru telah dikirim.', ['email' => $payload['email']]);

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal mengirim ulang OTP.', 500);
        }
    }

    // =========================================================================
    // 🔵 LOGIN & LOGOUT
    // =========================================================================

    /**
     * Memproses Login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $email = $request->input('email');

        try {
            return DB::transaction(function () use ($request, $email) {
                $credentials = $request->validate([
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                ]);

                $user = User::where('email', $email)->lockForUpdate()->first();

                if (! $user) {
                    Log::warning('Email tidak ditemukan.');

                    return $this->sendError('Email atau Password salah.', 401);
                }

                $lastUpdate = $user->updated_at;
                if ($user->failed_attempts > 0 && $lastUpdate->lt(now()->subMinutes(10))) {
                    $user->failed_attempts = 0;
                    $user->save();
                }

                if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                    Log::warning('Akun belum diverifikasi.');

                    return $this->sendError('Akun Anda belum diverifikasi, silakan daftar ulang.', 403, ['email' => $user->email], 'UNVERIFIED');
                }

                if ($user->locked_until && now()->lt($user->locked_until)) {
                    Log::warning('Akun dikunci.');

                    return $this->sendError('Email atau Password salah.', 401);
                }

                if (! Hash::check($credentials['password'], $user->password)) {
                    $user->increment('failed_attempts');

                    if ($user->failed_attempts >= 4) {
                        $lockMinutes = 10;
                        $user->update(['locked_until' => now()->addMinutes($lockMinutes), 'failed_attempts' => 0]);
                        Log::warning('Password salah, akun dikunci.');

                        return $this->sendError('Email atau Password salah.', 401);
                    }

                    $sisa = 4 - $user->failed_attempts;
                    Log::warning('Password salah.', ['sisa' => $sisa]);

                    return $this->sendError('Email atau Password salah.', 401);
                }

                $user->tokens()->delete();
                $tokenAuth = $user->createToken('auth_token')->plainTextToken;

                // Buat single-use token untuk reset password via email (disimpan di kolom khusus)
                $resetToken = Str::random(40);
                $user->update([
                    'failed_attempts'        => 0,
                    'locked_until'           => null,
                    'reset_token'            => hash('sha256', $resetToken),
                ]);

                // Kirim email notifikasi login (dibatasi 1 email per 5 menit per user untuk mencegah flooding)
                $emailRateLimitKey = 'login_notification_email_' . $user->user_id;
                if (!RateLimiter::tooManyAttempts($emailRateLimitKey, 1)) {
                    RateLimiter::hit($emailRateLimitKey, 300); // 5 menit (300 detik)
                    $ipAddress = $request->ip();
                    $userAgent = $request->header('User-Agent');
                    Mail::to($user->email)->send(new LoginNotificationMail($user, now()->format('Y-m-d H:i:s'), $ipAddress, $userAgent, $resetToken));
                } else {
                    Log::info('Email notifikasi login dilewati (rate limit aktif).', ['user_id' => $user->user_id]);
                }

                Log::info('Login berhasil.', ['user' => $user->user_id]);

                return $this->sendSuccess('Login berhasil!', [
                    'access_token' => $tokenAuth,
                    'token_type' => 'Bearer',
                    'user' => $this->formatUser($user),
                ]);
            });

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Memproses Logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            /** @var PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
            Log::info('Pengguna logout.', ['user_id' => $user->user_id]);

            return $this->sendSuccess('Berhasil logout.');
        }
        Log::warning('Token tidak valid.');

        return $this->sendError('Token tidak valid.', 401);
    }

    // =========================================================================
    // 🟡 LUPA & RESET PASSWORD
    // =========================================================================

    /**
     * Memproses permintaan email untuk reset password (mengirim OTP).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPasswordVerify(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);

            $emailLower = Str::lower($validatedData['email']);
            $otpCode = random_int(100000, 999999);

            Cache::put(
                "forgot_pass_{$emailLower}",
                ['otp_hash' => hash('sha256', (string) $otpCode)],
                now()->addMinutes(10)
            );

            Mail::to($validatedData['email'])->send(
                new SendOtpMail($otpCode, 'Emails.reset_password_otp', 'Kode Reset Password')
            );

            Log::info('OTP reset password dikirim.');

            return $this->sendSuccess('Kode OTP telah dikirim ke email Anda.', ['email' => $validatedData['email']]);

        } catch (ValidationException $e) {
            Log::warning('Email tidak ditemukan.');

            return $this->sendError('Email tidak ditemukan.', 422);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal mengirim OTP.', 500);
        }
    }

        /**
     * Memverifikasi OTP untuk reset password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPasswordOtpVerify(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email', 'otp' => 'required|numeric|digits:6']);

            $emailLower = Str::lower($request->email);
            $payload = Cache::get("forgot_pass_{$emailLower}");

            if (
                !$payload
                || !hash_equals($payload['otp_hash'] ?? '', hash('sha256', (string) $request->otp))
            ) {
                Log::warning('OTP reset salah atau kedaluwarsa.');
                return $this->sendError('Kode OTP salah atau telah kedaluwarsa.', 400);
            }

            Log::info('OTP valid.');

            return $this->sendSuccess('OTP valid. Silakan reset password.', ['email' => $request->email]);

        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Terjadi kesalahan sistem.', 500);
        }
    }

    /**
     * Mengirim ulang OTP untuk reset password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPasswordOtpResend(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email|exists:users,email']);

            $emailLower = Str::lower($request->email);
            $newOtpCode = random_int(100000, 999999);

            Cache::put(
                "forgot_pass_{$emailLower}",
                ['otp_hash' => hash('sha256', (string) $newOtpCode)],
                now()->addMinutes(10)
            );

            Mail::to($request->email)->send(
                new SendOtpMail($newOtpCode, 'Emails.reset_password_otp', 'Kode Reset Password')
            );

            Log::info('OTP reset baru dikirim.');

            return $this->sendSuccess('Kode OTP baru telah dikirim ke email Anda.', ['email' => $request->email]);

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal mengirim ulang OTP.', 500);
        }
    }

    /**
     * Memverifikasi validitas link reset password (email dan token) sebelum form disubmit.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPasswordCheck(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            // Verifikasi token dari kolom reset_token (harus terisi dan hash cocok)
            if (!$user || !$user->reset_token || !hash_equals($user->reset_token, hash('sha256', (string) $request->token))) {
                Log::warning('Cek validitas link reset: token tidak valid atau kadaluarsa.');
                return $this->sendError('Link pemulihan tidak valid atau sudah kadaluarsa.', 400);
            }

            return $this->sendSuccess('Link pemulihan valid.');

        } catch (ValidationException $e) {
            Log::warning('Cek validitas link reset: validasi error.', ['errors' => $e->errors()]);
            // Tampilkan pesan error umum ke user mobile untuk alasan keamanan/UX
            return $this->sendError('Link pemulihan tidak valid.', 400);
        } catch (Exception $e) {
            Log::error('Error sistem cek link reset.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan pada server.', 500);
        }
    }

    /**
     * Memproses penyimpanan password baru.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'email' => 'required|email|exists:users,email',
                    'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
                    'token' => 'required|string', // Pastikan wajib
                ]);

                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                // Verifikasi token dari kolom reset_token (terpisah dari otp_code)
                if (!$user->reset_token || !hash_equals($user->reset_token, hash('sha256', (string) $request->token))) {
                    Log::warning('Token reset tidak valid.');
                    return $this->sendError('Link pemulihan tidak valid atau sudah kadaluarsa (hanya bisa dipakai sekali).', 400);
                }

                if (Hash::check($request->password, $user->password)) {
                    Log::warning('Password baru sama dengan lama.');
                    return $this->sendError('Password baru tidak boleh sama dengan yang lama.', 400);
                }

                $user->password       = Hash::make($request->password);
                $user->reset_token            = null;  // Hapus token setelah dipakai (single-use)
                $user->otp_code       = null;
                $user->otp_expires_at = null;
                $user->failed_attempts = 0;
                $user->locked_until    = null;
                $user->save();

                // Hapus sesi login saat pemulihan akun (logout dari semua perangkat)
                $user->tokens()->delete();

                Log::info('Password direset dan sesi dibersihkan.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Password berhasil direset. Silakan login.');
            });

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal mereset password.', 500);
        }
    }

    /**
     * Memproses penyimpanan password baru menggunakan OTP (khusus Mobile).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPasswordByOtp(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'email' => 'required|email|exists:users,email',
                    'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
                    'token' => 'required|numeric|digits:6', // Dianggap sebagai OTP
                ]);

                $emailLower = Str::lower($request->email);
                $cacheKey = "forgot_pass_{$emailLower}";

                // Validasi OTP tanpa mengonsumsinya (Cache::get, bukan pull)
                $payload = Cache::get($cacheKey);
                if (
                    !$payload
                    || !hash_equals($payload['otp_hash'] ?? '', hash('sha256', (string) $request->token))
                ) {
                    Log::warning('OTP reset tidak valid atau kadaluarsa.');
                    return $this->sendError('Kode OTP tidak valid atau sudah kadaluarsa.', 400);
                }

                $user = User::where('email', $request->email)->lockForUpdate()->firstOrFail();

                if (Hash::check($request->password, $user->password)) {
                    Log::warning('Password baru sama dengan lama.');
                    return $this->sendError('Password baru tidak boleh sama dengan yang lama.', 400);
                }

                // konsumsi OTP (single-use) setelah validasi berhasil
                $claimed = Cache::pull($cacheKey);
                if (!$claimed) {
                    Log::warning('Race condition: cache OTP sudah di-pull request lain.');
                    return $this->sendError('Verifikasi sedang diproses atau sudah selesai.', 409);
                }

                $user->password        = Hash::make($request->password);
                $user->reset_token     = null; // Hapus juga token link untuk keamanan
                $user->failed_attempts = 0;
                $user->locked_until    = null;
                $user->save();

                // Hapus sesi login saat pemulihan akun (logout dari semua perangkat)
                $user->tokens()->delete();

                Log::info('Password direset via OTP dan sesi dibersihkan.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Password berhasil direset. Silakan login.');
            });

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal mereset password.', 500);
        }
    }
}
