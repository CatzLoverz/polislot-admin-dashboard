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
            return redirect()->route('otp_register.show')->with('swal_success_crud', 'Registrasi berhasil! Cek email Anda untuk kode OTP.');

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
            return redirect()->route('password.otp.verify.show')->with('swal_success_crud', 'Kode OTP telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('[AuthController@sendResetOtp] Gagal.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal mengirim OTP.');
        }
    }

    public function resendResetOtp(Request $request)
{
    $email = session('email_for_password_reset');

    if (!$email) {
        return redirect()->route('password.request')
            ->with('swal_error_crud', 'Sesi OTP Anda sudah berakhir. Silakan masukkan email kembali.');
    }

    try {
        $user = User::where('email', $email)->firstOrFail();
        $newOtpCode = rand(100000, 999999);
        $user->otp_code = $newOtpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send((new SendOtpMail($newOtpCode))->view('emails.reset_password_otp'));

        Log::info('[AuthController@resendResetOtp] SUKSES: OTP reset password baru dikirim.', ['email' => $email]);
        return back()->with('swal_success_crud', 'Kode OTP baru telah dikirim ke email Anda.');
    } catch (\Exception $e) {
        Log::error('[AuthController@resendResetOtp] Gagal mengirim OTP ulang.', ['error' => $e->getMessage()]);
        return back()->with('swal_error_crud', 'Gagal mengirim ulang OTP. Silakan coba lagi.');
    }
}

    public function showVerifyResetOtpForm()
{
    if (!session('email_for_password_reset')) {
        return redirect()->route('password.request');
    }

    Log::info('[AuthController@showVerifyResetOtpForm] Menampilkan halaman verifikasi OTP reset password.');
    return view('Contents.Auth.passwords.verify_reset_otp');
}

public function verifyResetOtp(Request $request)
{
    $request->validate(['otp' => 'required|numeric|digits:6']);
    $email = session('email_for_password_reset');

    try {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->otp_code !== $request->otp) {
            Log::warning('[AuthController@verifyResetOtp] OTP salah.', [
                'email' => $email,
                'input_otp' => $request->otp,
                'expected_otp' => $user->otp_code,
            ]);
            return back()->with('swal_error_crud', 'Kode OTP salah. Silakan cek kembali email Anda.');
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            Log::warning('[AuthController@verifyResetOtp] OTP kedaluwarsa.', [
                'email' => $email,
                'expired_at' => $user->otp_expires_at,
            ]);
            return back()->with('swal_error_crud', 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang OTP.');
        }

        // Simpan status berhasil verifikasi
        session()->put('otp_verified', true);

        Log::info('[AuthController@verifyResetOtp] OTP valid, lanjut ke halaman reset password.', ['email' => $email]);
        return redirect()->route('password.reset.show')->with('swal_success_crud', 'OTP berhasil diverifikasi! Silakan ubah password Anda.');
    } catch (\Exception $e) {
        Log::error('[AuthController@verifyResetOtp] Gagal verifikasi OTP.', ['error' => $e->getMessage()]);
        return back()->with('swal_error_crud', 'Terjadi kesalahan sistem saat verifikasi OTP.');
    }
}
    
    public function showResetPasswordForm()
{
    if (!session('email_for_password_reset')) {
        return redirect()->route('password.request');
    }

    if (!session('otp_verified')) {
        return redirect()->route('password.otp.verify.show');
    }

    Log::info('[AuthController@showResetPasswordForm] Menampilkan halaman reset password.');
    return view('Contents.Auth.passwords.reset');
}

    public function resetPassword(Request $request)
{
    $email = session('email_for_password_reset');
    Log::info('[AuthController@resetPassword] Memproses reset password.', ['email' => $email]);

    try {
        // === CEK STATUS SESI ===
        if (!$email || !session('otp_verified')) {
            Log::warning('[AuthController@resetPassword] Akses tidak sah ke halaman reset password.');
            return redirect()->route('password.request')
                ->with('swal_error_crud', 'Sesi reset password tidak valid. Silakan mulai ulang proses lupa password.');
        }

        // === VALIDASI PASSWORD ===
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                new ZxcvbnPassword(2)
            ],
        ]);

        $user = User::where('email', $email)->firstOrFail();

        // === UPDATE PASSWORD ===
        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;

        // === buka kunci akun & reset percobaan gagal ===
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->save();

        // === BERSIHKAN SESI ===
        session()->forget(['email_for_password_reset', 'otp_verified']);

        Log::info('[AuthController@resetPassword] SUKSES: Password berhasil direset.', ['email' => $email]);

        return redirect()->route('login.show')
            ->with('swal_success_crud', 'Password berhasil direset! Silakan login dengan password baru Anda.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('[AuthController@resetPassword] Validasi gagal.', ['errors' => $e->errors()]);

        // Ambil satu pesan error utama agar alert-nya jelas
        $errorMessage = collect($e->errors())->flatten()->first();

        return back()
            ->with('swal_error_crud', $errorMessage ?? 'Input tidak valid. Pastikan password memenuhi syarat keamanan.');
    } catch (\Exception $e) {
        Log::error('[AuthController@resetPassword] Gagal sistem.', ['error' => $e->getMessage()]);
        return back()
            ->with('swal_error_crud', 'Terjadi kesalahan pada server. Silakan coba lagi nanti.');
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

        $user = User::where('email', $email)->first();

        // 🔍 Jika user tidak ditemukan
        if (!$user) {
            Log::warning('[AuthController@login] GAGAL: Email tidak ditemukan.', ['email' => $email]);
            return back()
                ->with('swal_error_crud', 'Email tidak ditemukan.')
                ->withInput($request->only('email'));
        }

        // 🔒 Cek apakah akun sedang dikunci
        if ($user->locked_until && now()->lt($user->locked_until)) {
            // Hitung sisa menit, selalu bulat ke atas
            $minutes = now()->diffInSeconds($user->locked_until);
            $minutes = ceil($minutes / 60);

            Log::warning('[AuthController@login] AKUN TERKUNCI.', [
                'email' => $email,
                'locked_until' => $user->locked_until,
                'minutes_remaining' => $minutes,
            ]);

            return back()
                ->with('swal_error_crud', "Akun Anda dikunci. Coba lagi dalam {$minutes} menit.")
                ->withInput($request->only('email'));
        }

        // ✅ Coba autentikasi
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Reset percobaan gagal dan unlock akun
            $user->update([
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);

            Log::info('[AuthController@login] SUKSES: Autentikasi berhasil.', ['user_id' => $user->user_id]);

            return redirect()->intended(route('dashboard'))
                ->with('swal_success_login', 'Login Berhasil! Selamat datang, ' . $user->name . '.');
        }

        // ❌ Jika password salah, tambah percobaan gagal
        $user->increment('failed_attempts');
        Log::warning('[AuthController@login] GAGAL: Password salah.', [
            'email' => $email,
            'attempts' => $user->failed_attempts,
        ]);

        // 🔥 Jika sudah 3x gagal, kunci akun 15 menit
        if ($user->failed_attempts >= 3) {
            $lockMinutes = 15; // bisa diubah sesuai kebutuhan
            $user->update([
                'locked_until' => now()->addMinutes($lockMinutes),
                'failed_attempts' => 0, // reset setelah dikunci
            ]);

            Log::warning('[AuthController@login] AKUN DIKUNCI setelah 3 kali gagal login.', [
                'email' => $email,
                'locked_for_minutes' => $lockMinutes
            ]);

            return back()
                ->with('swal_error_crud', "Akun Anda dikunci selama {$lockMinutes} menit karena 3 kali gagal login.")
                ->withInput($request->only('email'));
        }

        // 🔁 Jika belum 3x, beri tahu sisa percobaan
        $sisa = 3 - $user->failed_attempts;
        return back()
            ->with('swal_error_crud', "Password salah. Kesempatan tersisa: {$sisa} kali.")
            ->withInput($request->only('email'));

    } catch (\Exception $e) {
        Log::error('[AuthController@login] ERROR SISTEM.', ['error' => $e->getMessage()]);
        return back()
            ->with('swal_error_crud', 'Terjadi kesalahan pada server.')
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
        return redirect()->route('login.show');
    }
}