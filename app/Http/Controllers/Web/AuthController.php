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
    // --- BAGIAN REGISTRASI & OTP ---
    public function showRegisterForm()
    {
        Log::info('[AuthController@showRegisterForm] Menampilkan halaman registrasi.');
        return view('Contents.Auth.register');
    }

    public function register(Request $request)
    {
        Log::info('[AuthController@register] Menerima permintaan registrasi baru.');
        try {
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
            Mail::to($user->email)->send(new SendOtpMail($otpCode));
            $request->session()->put('email_for_otp_verification', $user->email);
            return redirect()->route('otp.show')->with('swal_success_crud', 'Registrasi berhasil! Cek email Anda untuk kode OTP.');

        } catch (ValidationException $e) {
            Log::warning('[AuthController@register] GAGAL: Validasi gagal.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[AuthController@register] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('swal_error_crud', 'Terjadi kesalahan pada server.')->withInput();
        }
    }

    public function showRegisterOtpForm()
    {
        if (!session('email_for_otp_verification')) {
            return redirect()->route('register.show');
        }
        Log::info('[AuthController@showRegisterOtpForm] Menampilkan halaman verifikasi OTP.');
        return view('Contents.Auth.otp_in');
    }

    public function verifyRegisterOtp(Request $request)
    {
        $email = session('email_for_otp_verification');
        Log::info('[AuthController@verifyRegisterOtp] Menerima permintaan verifikasi OTP.', ['email' => $email]);
        try {
            $request->validate(['otp' => 'required|numeric|digits:6']);
            $user = User::where('email', $email)->firstOrFail();

            if ($user->otp_code !== $request->otp) {
                return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah.']);
            }
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return back()->withErrors(['otp' => 'Kode OTP Anda telah kedaluwarsa.']);
            }

            $user->email_verified_at = now();
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            session()->forget('email_for_otp_verification');
            Auth::login($user);

            Log::info('[AuthController@verifyRegisterOtp] SUKSES: Verifikasi OTP berhasil.', ['user_id' => $user->user_id]);
            return redirect()->route('dashboard')->with('swal_success_login', 'Verifikasi berhasil! Selamat datang.');
        } catch (\Exception $e) {
            Log::error('[AuthController@verifyRegisterOtp] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
        }
    }

    public function resendOtp(Request $request)
    {
        $email = session('email_for_otp_verification');
        if (!$email) return redirect()->route('register.show')->with('swal_error_crud', 'Sesi Anda telah berakhir.');
        
        Log::info('[AuthController@resendOtp] Menerima permintaan kirim ulang OTP registrasi.', ['email' => $email]);
        try {
            $user = User::where('email', $email)->firstOrFail();
            $newOtpCode = rand(100000, 999999);
            $user->otp_code = $newOtpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send((new SendOtpMail($newOtpCode))->view('emails.registration_otp'));
            Log::info('[AuthController@resendOtp] SUKSES: OTP registrasi baru dikirim.', ['user_id' => $user->user_id]);
            return back()->with('swal_success_crud', 'Kode OTP baru telah dikirim.');
        } catch (\Exception $e) {
            Log::error('[AuthController@resendOtp] GAGAL.', ['email' => $email, 'error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim ulang OTP.');
        }
    }

    public function showForgotPasswordForm()
    {
        Log::info('[AuthController@showForgotPasswordForm] Menampilkan halaman lupa password.');
        return view('Contents.Auth.forgot_pass');
    }

    public function sendResetOtp(Request $request)
    {
        Log::info('[AuthController@sendResetOtp] Menerima permintaan reset password.');
        try {
            $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);
            $user = User::where('email', $validatedData['email'])->first();
            $otpCode = rand(100000, 999999);

            $user->otp_code = $otpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            Log::info('Mengirim OTP reset password.', ['user_id' => $user->user_id]);
            Mail::to($user->email)->send((new SendOtpMail($otpCode))->view('emails.reset_password_otp'));
            
            $request->session()->put('email_for_password_reset', $user->email);
            return redirect()->route('password.reset.show')->with('swal_success_crud', 'Kode OTP telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('[AuthController@sendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim OTP.');
        }
    }

    public function showResetPasswordForm()
    {
        if (!session('email_for_password_reset')) return redirect()->route('password.request');
        Log::info('[AuthController@showResetPasswordForm] Menampilkan halaman reset password.');
        return view('Contents.Auth.passwords.reset');
    }

    public function resetPassword(Request $request)
    {
        $email = session('email_for_password_reset');
        Log::info('[AuthController@resetPassword] Memproses reset password.', ['email' => $email]);
        try {
            $request->validate([
                'otp' => 'required|numeric|digits:6',
                'password' => [
                    'required', 
                    'confirmed', 
                    PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                    new ZxcvbnPassword(2)
                ],
            ]);
            
            $user = User::where('email', $email)->firstOrFail();
            if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
                return back()->withErrors(['otp' => 'Kode OTP tidak valid atau telah kedaluwarsa.']);
            }

            $user->password = Hash::make($request->password);
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            session()->forget('email_for_password_reset');
            return redirect()->route('login.show')->with('swal_success_crud', 'Password berhasil direset! Silakan login.');
        } catch (\Exception $e) {
            Log::error('[AuthController@resetPassword] Gagal.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mereset password.');
        }
    }

    // --- BAGIAN LOGIN & LOGOUT ---
    public function showLoginForm()
    {
        try{
            if (Auth::check()) {
                Log::info('[AuthController@showLoginForm] Pengguna sudah login, mengalihkan ke dashboard.', ['user_id' => Auth::id()]);
                return redirect()->route('dashboard');
            }
            Log::info('[AuthController@showLoginForm] Menampilkan halaman login.');
            return view('Contents.Auth.login');
        } catch (\Exception $e) {
            Log::error('[AuthController@showLoginForm] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
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
                'password' => 'required|string',
            ]);

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();
                $user = Auth::user();
                Log::info('[AuthController@login] SUKSES: Autentikasi berhasil.', ['user_id' => $user->user_id]);
                return redirect()->intended(route('dashboard'))->with('swal_success_login', 'Login Berhasil! Selamat datang, ' . $user->name . '.');
            }

            Log::warning('[AuthController@login] GAGAL: Kredensial tidak valid.', ['email' => $email]);
            return back()->withErrors(['auth_error' => 'Email atau password salah.'])->withInput($request->only('email'));
        } catch (\Exception $e) {
            Log::error('[AuthController@login] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.')->withInput($request->only('email'));
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
        return redirect()->route('login.show');
    }
}