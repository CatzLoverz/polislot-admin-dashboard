<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    public function show()
    {
        if (!session('email_for_otp_verification')) {
            Log::warning('[OtpController@show] Akses halaman OTP tanpa session email, mengalihkan ke registrasi.');
            return redirect()->route('register');
        }
        Log::info('[OtpController@show] Menampilkan halaman verifikasi OTP.');
        // Anda akan membuat view ini sendiri
        return view('auth.otp_verification');
    }

    public function verify(Request $request)
    {
        $email = session('email_for_otp_verification');
        Log::info('[OtpController@verify] Menerima permintaan verifikasi OTP.', ['email' => $email]);

        try {
            $request->validate(['otp' => 'required|numeric|digits:6']);

            if (!$email) {
                return redirect()->route('login')->withErrors(['auth_error' => 'Sesi verifikasi Anda telah berakhir, silakan login.']);
            }

            $user = User::where('email', $email)->first();

            if (!$user || $user->otp_code !== $request->otp) {
                Log::warning('[OtpController@verify] GAGAL: Kode OTP tidak valid.', ['email' => $email, 'input_otp' => $request->otp]);
                return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah.']);
            }

            if (Carbon::now()->gt($user->otp_expires_at)) {
                Log::warning('[OtpController@verify] GAGAL: Kode OTP kedaluwarsa.', ['email' => $email]);
                return back()->withErrors(['otp' => 'Kode OTP Anda telah kedaluwarsa, silakan daftar ulang.']);
            }

            // Jika OTP valid dan tidak kedaluwarsa
            $user->email_verified_at = now();
            $user->otp_code = null; // Hapus OTP setelah berhasil
            $user->otp_expires_at = null;
            $user->save();

            session()->forget('email_for_otp_verification');
            Auth::login($user);

            Log::info('[OtpController@verify] SUKSES: Verifikasi OTP berhasil, pengguna dialihkan ke dashboard.', ['user_id' => $user->user_id]);
            return redirect()->route('dashboard')->with('swal_success_login', 'Verifikasi berhasil! Selamat datang di dashboard ' . $user->name . '.');

        } catch (\Exception $e) {
            Log::error('[OtpController@verify] GAGAL: Terjadi error sistem saat verifikasi OTP.', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('swal_error', 'Terjadi kesalahan pada server, silakan coba lagi.');
        }
    }
}