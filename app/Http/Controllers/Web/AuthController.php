<?php

namespace App\Http\Controllers\Web;

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

class AuthController extends Controller
{
    // --- BAGIAN REGISTRASI & VERIFIKASI OTP ---
    // (Tidak ada perubahan di bagian ini, sudah sinkron)

    public function registerForm()
    {
        Log::info('[AuthController@registerForm] Menampilkan halaman registrasi.');
        return view('Contents.Auth.register');
    }

    public function register(Request $request)
    {
        Log::info('[AuthController@register] Menerima permintaan registrasi baru.');
        try {
            // Logika untuk mengizinkan pendaftaran ulang jika akun belum terverifikasi
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
            $request->session()->put('email_for_otp_verification', $user->email);
            return redirect()->route('register_otp.form')->with('swal_success_crud', 'Registrasi berhasil! Cek email Anda untuk kode OTP.');

        } catch (ValidationException $e) {
            Log::warning('[AuthController@register] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[AuthController@register] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('swal_error_crud', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function registerOtpForm()
    {
        if (!session('email_for_otp_verification')) {
            return redirect()->route('register.form');
        }
        Log::info('[AuthController@registerOtpForm] Menampilkan halaman verifikasi OTP.');
        return view('Contents.Auth.register_otp');
    }

    public function registerOtpVerify(Request $request)
    {
        $email = session('email_for_otp_verification');
        Log::info('[AuthController@registerOtpVerify] Menerima permintaan verifikasi OTP.', ['email' => $email]);
        try {
            $request->validate(['otp' => 'required|numeric|digits:6']);
            $user = User::where('email', $email)->firstOrFail();

            if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                return back()->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa.']);
            }

            $user->email_verified_at = now();
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            session()->forget('email_for_otp_verification');
            Auth::login($user);

            Log::info('[AuthController@registerOtpVerify] SUKSES: Verifikasi OTP berhasil.', ['user_id' => $user->user_id]);
            return redirect()->route('dashboard')->with('swal_success_login', 'Verifikasi berhasil! Selamat datang.');
        } catch (\Exception $e) {
            Log::error('[AuthController@registerOtpVerify] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }

    public function registerOtpResend(Request $request)
    {
        $email = session('email_for_otp_verification');
        if (!$email) return redirect()->route('register.form')->with('swal_error_crud', 'Sesi Anda telah berakhir.');

        Log::info('[AuthController@registerOtpResend] Menerima permintaan kirim ulang OTP registrasi.', ['email' => $email]);
        try {
            $user = User::where('email', $email)->firstOrFail();
            $newOtpCode = rand(100000, 999999);
            $user->otp_code = $newOtpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'emails.registration_otp'));
            Log::info('[AuthController@registerOtpResend] SUKSES: OTP registrasi baru dikirim.', ['user_id' => $user->user_id]);
            return back()->with('swal_success_crud', 'Kode OTP baru telah dikirim.');
        } catch (\Exception $e) {
            Log::error('[AuthController@registerOtpResend] GAGAL.', ['email' => $email, 'error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim ulang OTP.');
        }
    }

    // --- BAGIAN LUPA & RESET PASSWORD ---

    public function forgotPasswordForm()
    {
        Log::info('[AuthController@forgotPasswordForm] Menampilkan halaman lupa password.');
        return view('Contents.Auth.ForgotPass.forgot_form');
    }

    public function forgotPasswordVerify(Request $request)
    {
        Log::info('[AuthController@forgotPasswordVerify] Menerima permintaan reset password.');
        try {
            $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);
            $user = User::where('email', $validatedData['email'])->first();
            $otpCode = rand(100000, 999999);

            $user->otp_code = $otpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            Log::info('Mengirim OTP reset password.', ['user_id' => $user->user_id]);
            Mail::to($user->email)->send(new SendOtpMail($otpCode, 'emails.reset_password_otp'));

            $request->session()->put('email_for_password_reset', $user->email);
            return redirect()->route('forgot_otp.form')->with('swal_success_crud', 'Kode OTP telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('[AuthController@sendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim OTP.');
        }
    }

    public function forgotPasswordOtpResend(Request $request)
    {
        $email = session('email_for_password_reset');
        if (!$email) {
            return redirect()->route('forgot.form')->with('swal_error_crud', 'Sesi OTP Anda sudah berakhir.');
        }
        try {
            $user = User::where('email', $email)->firstOrFail();
            $newOtpCode = rand(100000, 999999);
            $user->otp_code = $newOtpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'emails.reset_password_otp'));
            Log::info('[AuthController@resendResetOtp] SUKSES: OTP reset baru dikirim.', ['email' => $email]);
            return back()->with('swal_success_crud', 'Kode OTP baru telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('[AuthController@resendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim ulang OTP.');
        }
    }

    public function forgotPasswordOtpForm()
    {
        if (!session('email_for_password_reset')) {
            return redirect()->route('forgot.form');
        }
        Log::info('[AuthController@showVerifyResetOtpForm] Menampilkan halaman verifikasi OTP reset password.');
        return view('Contents.Auth.ForgotPass.forgot_otp');
    }

    public function forgotPasswordOtpVerify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);
        $email = session('email_for_password_reset');
        try {
            $user = User::where('email', $email)->firstOrFail();
            if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                return back()->with('swal_error_crud', 'Kode OTP salah atau telah kedaluwarsa.');
            }
            session()->put('otp_verified', true);
            Log::info('[AuthController@verifyResetOtp] OTP valid, lanjut ke reset password.', ['email' => $email]);
            return redirect()->route('reset_pass.form')->with('swal_success_crud', 'OTP berhasil diverifikasi!');
        } catch (\Exception $e) {
            Log::error('[AuthController@verifyResetOtp] Gagal verifikasi.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan sistem.');
        }
    }

    public function resetPasswordForm()
    {
        if (!session('email_for_password_reset') || !session('otp_verified')) {
            // PENYESUAIAN ROUTE: 'password.request' diubah menjadi 'forgot.form'
            return redirect()->route('forgot.form');
        }
        Log::info('[AuthController@showResetPasswordForm] Menampilkan halaman reset password.');
        return view('Contents.Auth.ForgotPass.forgot_new_pass');
    }

    public function resetPassword(Request $request)
    {
        $email = session('email_for_password_reset');
        if (!$email || !session('otp_verified')) {
            return redirect()->route('forgot.form')->with('swal_error_crud', 'Sesi tidak valid.');
        }
        try {
            $request->validate([
                'password' => ['required','confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols(), new ZxcvbnPassword(2)],
            ]);
            $user = User::where('email', $email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            session()->forget(['email_for_password_reset', 'otp_verified']);
            Log::info('[AuthController@resetPassword] SUKSES: Password direset.', ['email' => $email]);
            return redirect()->route('login.form')->with('swal_success_crud', 'Password berhasil direset! Silakan login.');
        } catch (ValidationException $e) {
            $errorMessage = collect($e->errors())->flatten()->first();
            return back()->with('swal_error_crud', $errorMessage ?? 'Input tidak valid.');
        } catch (\Exception $e) {
            Log::error('[AuthController@resetPassword] Gagal sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }

    // --- BAGIAN LOGIN & LOGOUT ---
    // (Tidak ada perubahan di bagian ini, sudah sinkron)

    public function loginForm()
    {
        try{
            if (Auth::check()) {
                Log::info('[AuthController@loginForm] Pengguna sudah login, mengalihkan ke dashboard.', ['user_id' => Auth::id()]);
                return redirect()->route('dashboard');
            }
            Log::info('[AuthController@loginForm] Menampilkan halaman login.');
            return view('Contents.Auth.login');
        } catch (\Exception $e) {
            Log::error('[AuthController@loginForm] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }

    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        Log::info("[AuthController@login] Menerima percobaan login untuk email: {$email}");

        try {
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            $user = User::where('email', $email)->first();

            if (!$user) {
                return back()->with('swal_error_crud', 'Email tidak ditemukan.')->withInput($request->only('email'));
            }

            // ✅ Logika baru: Admin tidak perlu verifikasi akun
            if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                Log::warning('[AuthController@login] GAGAL: Akun belum diverifikasi.', ['email' => $email]);
                $request->session()->put('email_for_otp_verification', $user->email);
                return back()
                    ->with('swal_error_crud', 'Akun Anda belum diverifikasi. Silakan Lakukan Pendaftaran Ulang.');
            }

            // 🔒 Cek apakah akun sedang dikunci
            if ($user->locked_until && now()->lt($user->locked_until)) {
                $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
                return back()->with('swal_error_crud', "Akun Anda dikunci. Coba lagi dalam {$minutes} menit.")
                    ->withInput($request->only('email'));
            }

            // 🔐 Proses login
            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();
                $user->update(['failed_attempts' => 0, 'locked_until' => null]);

                Log::info('[AuthController@login] SUKSES.', [
                    'user_id' => $user->user_id,
                    'role' => $user->role
                ]);

                return redirect()->intended(route('dashboard'))
                    ->with('swal_success_login', 'Login Berhasil! Selamat datang, ' . $user->name . '.');
            }

            // ❌ Password salah
            $user->increment('failed_attempts');
            Log::warning('[AuthController@login] GAGAL: Password salah.', ['attempts' => $user->failed_attempts]);

            if ($user->failed_attempts >= 3) {
                $lockMinutes = 15;
                $user->update(['locked_until' => now()->addMinutes($lockMinutes), 'failed_attempts' => 0]);
                return back()->with('swal_error_crud', "Akun Anda dikunci selama {$lockMinutes} menit.")
                    ->withInput($request->only('email'));
            }

            $sisa = 3 - $user->failed_attempts;
            return back()->with('swal_error_crud', "Password salah. Sisa percobaan: {$sisa} kali.")
                ->withInput($request->only('email'));

        } catch (\Exception $e) {
            Log::error('[AuthController@login] ERROR SISTEM.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.')
                ->withInput($request->only('email'));
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Log::info('[AuthController@logout] Memulai proses logout.', ['user_id' => $user->user_id]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::info('[AuthController@logout] SUKSES: Pengguna telah logout.');
        return redirect()->route('login.form');
    }
}