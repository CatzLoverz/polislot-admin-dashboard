<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function loginForm()
    {
        try {
            if (Auth::check()) {
                return redirect()->route('dashboard');
            }
            return view('Contents.Auth.login');
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@loginForm] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }

    /**
     * Memproses permintaan login pengguna.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $email = $request->input('email');

        try {
            return DB::transaction(function () use ($request, $email) {
                $credentials = $request->validate([
                    'email' => 'required|string|email',
                    'password' => 'required|string'
                ]);

                $user = User::where('email', $email)->where('role', 'admin')->first();

                if (!$user) {
                    Log::warning('[WEB AuthController@login] Gagal: Email tidak ditemukan.');
                    return redirect()->route('login.form')->with('swal_error_crud', 'Email tidak ditemukan.')->withInput($request->only('email'));
                }

                $lastUpdate = $user->updated_at;
                if ($user->failed_attempts > 0 && $lastUpdate->lt(now()->subMinutes(10))) {
                    $user->failed_attempts = 0;
                    $user->save();
                }

                if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
                    Log::warning('[WEB AuthController@login] Gagal: Akun belum diverifikasi.');
                    $request->session()->put('email_for_otp_verification', $user->email);
                    return redirect()->route('login.form')
                        ->with('swal_error_crud', 'Akun Anda belum diverifikasi. Silakan Lakukan Pendaftaran Ulang.');
                }

                if ($user->locked_until && now()->lt($user->locked_until)) {
                    $minutes = ceil(now()->diffInSeconds($user->locked_until) / 60);
                    Log::warning('[WEB AuthController@login] Gagal: Akun dikunci.');
                    return redirect()->route('login.form')->with('swal_error_crud', "Akun Anda dikunci. Coba lagi dalam {$minutes} menit.")
                        ->withInput($request->only('email'));
                }

                if (Auth::attempt($credentials, $request->filled('remember'))) {
                    $request->session()->regenerate();
                    $user->update(['failed_attempts' => 0, 'locked_until' => null]);

                    Log::info('[WEB AuthController@login] Sukses: Login berhasil.');

                    return redirect()->intended(route('dashboard'))
                        ->with('swal_success_login', 'Login Berhasil! Selamat datang, ' . $user->name . '.');
                }

                $user->increment('failed_attempts');
                
                if ($user->failed_attempts >= 4) {
                    $lockMinutes = 10;
                    $user->update(['locked_until' => now()->addMinutes($lockMinutes), 'failed_attempts' => 0]);
                    Log::warning('[WEB AuthController@login] Gagal: Password salah, akun dikunci.');
                    return redirect()->route('login.form')->with('swal_error_crud', "Akun Anda dikunci selama {$lockMinutes} menit.")
                        ->withInput($request->only('email'));
                }

                $sisa = 4 - $user->failed_attempts;
                Log::warning('[WEB AuthController@login] Gagal: Password salah.');
                return redirect()->route('login.form')->with('swal_error_crud', "Password salah. Sisa percobaan: {$sisa} kali.")
                    ->withInput($request->only('email'));
            });

        } catch (\Exception $e) {
            Log::error('[WEB AuthController@login] Gagal: Error sistem.');
            return redirect()->route('login.form')->with('swal_error_crud', 'Terjadi kesalahan pada server.')
                ->withInput($request->only('email'));
        }
    }

    /**
     * Memproses permintaan logout pengguna.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
            $user = Auth::user();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            Log::info('[WEB AuthController@logout] Sukses: Pengguna telah logout.');
            return redirect()->route('login.form');    
        });
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@logout] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }

    // =========================================================================
    // 🟡 ALUR LUPA & RESET PASSWORD
    // =========================================================================

    /**
     * Menampilkan halaman form lupa password.
     * * @return \Illuminate\View\View
     */
    public function forgotPasswordForm()
    {
        return view('Contents.Auth.ForgotPass.forgot_form');
    }

    /**
     * Memproses permintaan email untuk reset password (mengirim OTP).
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgotPasswordVerify(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate(['email' => 'required|email|exists:users,email']);
                $validatedData = $request->only('email');
                $user = User::where('email', $validatedData['email'])->first();
                $otpCode = rand(100000, 999999);

                $user->otp_code = $otpCode;
                $user->otp_expires_at = Carbon::now()->addMinutes(10);
                $user->save();

                Mail::to($user->email)->send(new SendOtpMail($otpCode, 'Emails.reset_password_otp', 'Kode Reset Password Anda'));

                $request->session()->put('email_for_password_reset', $user->email);
                
                Log::info('[WEB AuthController@forgotPasswordVerify] Sukses: OTP reset password dikirim.');
                
                return redirect()->route('forgot_otp.form')->with('swal_success_crud', 'Kode OTP telah dikirim ke email Anda.');
            });
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@forgotPasswordVerify] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim OTP.');
        }
    }

    /**
     * Menampilkan halaman form verifikasi OTP untuk reset password.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function forgotPasswordOtpForm()
    {
        if (!session('email_for_password_reset')) {
            return redirect()->route('forgot.form');
        }
        return view('Contents.Auth.ForgotPass.forgot_otp');
    }

    /**
     * Memproses verifikasi OTP untuk reset password.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgotPasswordOtpVerify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);
        $email = session('email_for_password_reset');
        try {
            return DB::transaction(function () use ($request, $email) {
                $user = User::where('email', $email)->firstOrFail();
                if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                    Log::warning('[WEB AuthController@forgotPasswordOtpVerify] Gagal: OTP salah atau kedaluwarsa.');
                    return back()->with('swal_error_crud', 'Kode OTP salah atau telah kedaluwarsa.');
                }
                session()->put('otp_verified', true);
                
                Log::info('[WEB AuthController@forgotPasswordOtpVerify] Sukses: OTP valid.');
                return redirect()->route('reset_pass.form')->with('swal_success_crud', 'OTP berhasil diverifikasi!');
            });
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@forgotPasswordOtpVerify] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Mengirim ulang OTP untuk reset password.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgotPasswordOtpResend(Request $request)
    {
        $email = session('email_for_password_reset');
        if (!$email) {
            return redirect()->route('forgot.form')->with('swal_error_crud', 'Sesi OTP Anda sudah berakhir.');
        }
        try {
            return DB::transaction(function () use ($email) {
                $user = User::where('email', $email)->firstOrFail();
                $newOtpCode = rand(100000, 999999);
                $user->otp_code = $newOtpCode;
                $user->otp_expires_at = Carbon::now()->addMinutes(10);
                $user->save();

                Mail::to($user->email)->send(new SendOtpMail($newOtpCode, 'Emails.reset_password_otp', 'Kode Reset Password Anda'));
                
                Log::info('[WEB AuthController@forgotPasswordOtpResend] Sukses: OTP reset baru dikirim.');
                
                return back()->with('swal_success_crud', 'Kode OTP baru telah dikirim ke email Anda.');
            });
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@forgotPasswordOtpResend] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim ulang OTP.');
        }
    }

    /**
     * Menampilkan halaman form untuk memasukkan password baru.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function resetPasswordForm()
    {
        if (!session('email_for_password_reset') || !session('otp_verified')) {
            return redirect()->route('forgot.form');
        }
        return view('Contents.Auth.ForgotPass.forgot_new_pass');
    }

    /**
     * Memproses penyimpanan password baru setelah reset.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
            return DB::transaction(function () use ($request, $email, $passwordRules) {
                $request->validate([
                    'password' => $passwordRules,
                ]);
                $user = User::where('email', $email)->firstOrFail();

                if (Hash::check($request->password, $user->password)) {
                    Log::warning('[WEB AuthController@resetPassword] Gagal: Password baru sama dengan lama.');
                    return back()->with('swal_error_crud', 'Password baru tidak boleh sama dengan password lama.');
                }

                $user->password = Hash::make($request->password);
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->failed_attempts = 0;
                $user->locked_until = null;
                $user->save();
                session()->forget(['email_for_password_reset', 'otp_verified']);
                
                Log::info('[WEB AuthController@resetPassword] Sukses: Password direset.');
                return redirect()->route('login.form')->with('swal_success_crud', 'Password berhasil direset! Silakan login.');
            });
        } catch (ValidationException $e) {
            Log::warning('[WEB AuthController@resetPassword] Gagal: Validasi data gagal.', [ 'errors' => $e->errors() ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('[WEB AuthController@resetPassword] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }
}