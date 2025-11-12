<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    // --- AUTENTIKASI UTAMA (LOGIN & LOGOUT) ---

    /**
     * Menampilkan halaman login.
     */
    public function loginForm()
    {
        try {
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

    /**
     * Memproses permintaan login pengguna.
     */
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
                return redirect()->route('login.form')->with('swal_error_crud', 'Email tidak ditemukan.')->withInput($request->only('email'));
            }

            if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                Log::warning('[AuthController@login] GAGAL: Akun belum diverifikasi.', ['email' => $email]);
                $request->session()->put('email_for_otp_verification', $user->email);
                return redirect()->route('login.form')
                    ->with('swal_error_crud', 'Akun Anda belum diverifikasi. Silakan Lakukan Pendaftaran Ulang.');
            }

            if ($user->locked_until && now()->lt($user->locked_until)) {
                $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
                return redirect()->route('login.form')->with('swal_error_crud', "Akun Anda dikunci. Coba lagi dalam {$minutes} menit.")
                    ->withInput($request->only('email'));
            }

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

            $user->increment('failed_attempts');
            Log::warning('[AuthController@login] GAGAL: Password salah.', ['attempts' => $user->failed_attempts]);

            if ($user->failed_attempts >= 3) {
                $lockMinutes = 15;
                $user->update(['locked_until' => now()->addMinutes($lockMinutes), 'failed_attempts' => 0]);
                return redirect()->route('login.form')->with('swal_error_crud', "Akun Anda dikunci selama {$lockMinutes} menit.")
                    ->withInput($request->only('email'));
            }

            $sisa = 3 - $user->failed_attempts;
            return redirect()->route('login.form')->with('swal_error_crud', "Password salah. Sisa percobaan: {$sisa} kali.")
                ->withInput($request->only('email'));
        } catch (\Exception $e) {
            Log::error('[AuthController@login] ERROR SISTEM.', ['error' => $e->getMessage()]);
            return redirect()->route('login.form')->with('swal_error_crud', 'Terjadi kesalahan pada server.')
                ->withInput($request->only('email'));
        }
    }

    /**
     * Memproses permintaan logout pengguna.
     */
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


    // --- ALUR LUPA & RESET PASSWORD ---

    /**
     * Menampilkan halaman form lupa password.
     */
    public function forgotPasswordForm()
    {
        Log::info('[AuthController@forgotPasswordForm] Menampilkan halaman lupa password.');
        return view('Contents.Auth.ForgotPass.forgot_form');
    }

    /**
     * Memproses permintaan email untuk reset password (mengirim OTP).
     */
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

    /**
     * Menampilkan halaman form verifikasi OTP untuk reset password.
     */
    public function forgotPasswordOtpForm()
    {
        if (!session('email_for_password_reset')) {
            return redirect()->route('forgot.form');
        }
        Log::info('[AuthController@showVerifyResetOtpForm] Menampilkan halaman verifikasi OTP reset password.');
        return view('Contents.Auth.ForgotPass.forgot_otp');
    }

    /**
     * Memproses verifikasi OTP untuk reset password.
     */
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

    /**
     * Mengirim ulang OTP untuk reset password.
     */
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

    /**
     * Menampilkan halaman form untuk memasukkan password baru.
     */
    public function resetPasswordForm()
    {
        if (!session('email_for_password_reset') || !session('otp_verified')) {

            return redirect()->route('forgot.form');
        }
        Log::info('[AuthController@showResetPasswordForm] Menampilkan halaman reset password.');
        return view('Contents.Auth.ForgotPass.forgot_new_pass');
    }

    /**
     * Memproses penyimpanan password baru setelah reset.
     */
    public function resetPassword(Request $request)
    {
        $email = session('email_for_password_reset');
        if (!$email || !session('otp_verified')) {
            return redirect()->route('forgot.form')->with('swal_error_crud', 'Sesi tidak valid.');
        }

        $passwordRules = [
            'required',
            'confirmed',
            PasswordRule::min(8)->mixedCase()->numbers()->symbols(), 
        ];

        try {
            $request->validate([
                'password' => $passwordRules,
            ]);
            $user = User::where('email', $email)->firstOrFail();

            if (Hash::check($request->password, $user->password)) {
                return back()->with('swal_error_crud', 'Password baru tidak boleh sama dengan password lama.');
            }

            $user->password = Hash::make($request->password);
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->failed_attempts = 0;
            $user->locked_until = null;
            $user->save();
            session()->forget(['email_for_password_reset', 'otp_verified']);
            Log::info('[AuthController@resetPassword] SUKSES: Password direset.', ['email' => $email]);
            return redirect()->route('login.form')->with('swal_success_crud', 'Password berhasil direset! Silakan login.');
        } catch (ValidationException $e) {
            Log::warning('[AuthController@resetPassword] GAGAL: Validasi data gagal.', [ 'email' => $email, 'errors' => $e->errors() ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('[AuthController@resetPassword] Gagal sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }
}