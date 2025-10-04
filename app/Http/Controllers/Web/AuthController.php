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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            ]);

            $defaultPassword = Str::slug($validatedData['name'], '_') . '_123';
            $otpCode = rand(100000, 999999);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($defaultPassword),
                'role' => 'user',
                'pass_change' => false,
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

    public function showOtpForm()
    {
        if (!session('email_for_otp_verification')) {
            return redirect()->route('register.show');
        }
        Log::info('[AuthController@showOtpForm] Menampilkan halaman verifikasi OTP.');
        return view('Contents.Auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $email = session('email_for_otp_verification');
        Log::info('[AuthController@verifyOtp] Menerima permintaan verifikasi OTP.', ['email' => $email]);
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

            Log::info('[AuthController@verifyOtp] SUKSES: Verifikasi OTP berhasil.', ['user_id' => $user->user_id]);
            return redirect()->route('dashboard')->with('swal_success_login', 'Verifikasi berhasil! Selamat datang.');
        } catch (\Exception $e) {
            Log::error('[AuthController@verifyOtp] GAGAL: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server.');
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