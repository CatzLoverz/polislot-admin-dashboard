<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // 🟡 HALAMAN LOGIN
    // =========================================================================

    #[Test]
    public function login_form_dapat_diakses_jika_belum_login()
    {
        $response = $this->get(route('login.form'));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.Auth.login');
    }

    #[Test]
    public function login_form_redirect_ke_dashboard_jika_sudah_login()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($user);

        $response = $this->get(route('login.form'));

        $response->assertRedirect(route('dashboard'));
    }

    // =========================================================================
    // 🟡 PROSES LOGIN
    // =========================================================================

    #[Test]
    public function login_berhasil_untuk_admin_dan_reset_lock_data()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'failed_attempts' => 2, // Simulasi pernah gagal sebelumnya
            'locked_until' => null
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('swal_success_login');
        $this->assertAuthenticatedAs($user);

        // Pastikan data lock direset
        $user->refresh();
        $this->assertEquals(0, $user->failed_attempts);
    }

    #[Test]
    public function login_gagal_jika_bukan_admin_dianggap_email_tidak_ditemukan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user', // Role bukan admin
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'user@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('login.form'));
        // Pesan error sesuai query: Email tidak ditemukan (karena difilter where role admin)
        $response->assertSessionHas('swal_error_crud', 'Email tidak ditemukan.');
        $this->assertGuest();
    }

    #[Test]
    public function login_gagal_email_tidak_terdaftar()
    {
        $response = $this->post(route('login.attempt'), [
            'email' => 'unknown@test.com',
            'password' => 'pass',
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('swal_error_crud', 'Email tidak ditemukan.');
    }

    #[Test]
    public function login_gagal_password_salah_dan_increment_percobaan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('CorrectPass'),
            'role' => 'admin',
            'failed_attempts' => 0
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'WrongPass',
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('swal_error_crud');
        // Cek pesan sisa percobaan
        $response->assertSessionHas('swal_error_crud', 'Password salah. Sisa percobaan: 3 kali.');

        $user->refresh();
        $this->assertEquals(1, $user->failed_attempts);
    }

    #[Test]
    public function login_mengunci_akun_setelah_4_kali_gagal()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'role' => 'admin',
            'failed_attempts' => 3 // Sudah 3 kali gagal
        ]);

        // Gagal ke-4
        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'WrongPass',
        ]);

        $response->assertRedirect(route('login.form'));
        // Cek pesan terkunci
        $this->assertStringContainsString('Akun Anda dikunci selama 10 menit', session('swal_error_crud'));

        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertEquals(0, $user->failed_attempts); // Reset attempts saat terkunci
    }

    #[Test]
    public function login_ditolak_jika_akun_sedang_terkunci()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'role' => 'admin',
            'locked_until' => now()->addMinutes(5), // Masih terkunci 5 menit
            'password' => Hash::make('CorrectPass')
        ]);

        // Coba login password benar pun tetap ditolak
        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'CorrectPass',
        ]);

        $response->assertRedirect(route('login.form'));
        $this->assertStringContainsString('Akun Anda dikunci', session('swal_error_crud'));
        $this->assertGuest();
    }

    #[Test]
    public function login_reset_failed_attempts_jika_sudah_lewat_10_menit_sejak_gagal_terakhir()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'role' => 'admin',
            'failed_attempts' => 2,
            'password' => Hash::make('CorrectPass'),
            'updated_at' => now()->subMinutes(15) // Gagal terakhir 15 menit lalu
        ]);

        // Login lagi dengan password SALAH
        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'WrongPass',
        ]);

        $user->refresh();
        // Logika: 
        // 1. Cek time > 10 min -> Reset attempts jadi 0.
        // 2. Cek Password -> Salah -> Increment jadi 1.
        $this->assertEquals(1, $user->failed_attempts); 
    }

    #[Test]
    public function login_menangani_exception_sistem()
    {
        DB::shouldReceive('transaction')->andThrow(new \Exception('DB Down'));

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'pass'
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('swal_error_crud', 'Terjadi kesalahan pada server.');
    }

    // =========================================================================
    // 🟡 LOGOUT
    // =========================================================================

    #[Test]
    public function logout_berhasil_dan_hapus_session()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login.form'));
        $this->assertGuest();
    }

    // =========================================================================
    // 🟡 FORGOT PASSWORD (FORM & SEND OTP)
    // =========================================================================

    #[Test]
    public function forgot_form_dapat_diakses()
    {
        $response = $this->get(route('forgot.form'));
        $response->assertStatus(200);
        $response->assertViewIs('Contents.Auth.ForgotPass.forgot_form');
    }

    #[Test]
    public function forgot_verify_mengirim_otp_dan_set_session()
    {
        Mail::fake();
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['email' => 'forgetful@test.com']);

        $response = $this->post(route('forgot.attempt'), [
            'email' => 'forgetful@test.com'
        ]);

        $response->assertRedirect(route('forgot_otp.form'));
        $response->assertSessionHas('swal_success_crud', 'Kode OTP telah dikirim ke email Anda.');
        
        // Cek session
        $this->assertEquals('forgetful@test.com', session('email_for_password_reset'));
        
        // Cek DB & Mail
        $user->refresh();
        $this->assertNotNull($user->otp_code);
        Mail::assertSent(SendOtpMail::class);
    }

    #[Test]
    public function forgot_verify_gagal_jika_email_tidak_ada()
    {
        $response = $this->post(route('forgot.attempt'), [
            'email' => 'ghost@test.com'
        ]);

        // Controller menangkap ValidationException sebagai Exception umum
        // sehingga return-nya adalah swal_error_crud, bukan session errors biasa.
        $response->assertSessionHas('swal_error_crud', 'Gagal mengirim OTP.');
    }

    // =========================================================================
    // 🟡 OTP VERIFY (FORM & ACTION)
    // =========================================================================

    #[Test]
    public function otp_form_redirect_jika_tidak_ada_session_email()
    {
        // Akses langsung tanpa melalui forgot password
        $response = $this->get(route('forgot_otp.form'));
        $response->assertRedirect(route('forgot.form'));
    }

    #[Test]
    public function otp_verify_sukses_redirect_ke_reset_password()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'otp@test.com',
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        // Simulasi session dari langkah sebelumnya
        session()->put('email_for_password_reset', 'otp@test.com');

        $response = $this->post(route('forgot_otp.verify'), [
            'otp' => '123456'
        ]);

        $response->assertRedirect(route('reset_pass.form'));
        $this->assertTrue(session('otp_verified'));
    }

    #[Test]
    public function otp_verify_gagal_otp_salah()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'otp@test.com',
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        session()->put('email_for_password_reset', 'otp@test.com');

        $response = $this->post(route('forgot_otp.verify'), [
            'otp' => '999999' // Salah
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('swal_error_crud', 'Kode OTP salah atau telah kedaluwarsa.');
        $this->assertFalse(session()->has('otp_verified'));
    }

    #[Test]
    public function otp_verify_gagal_otp_expired()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'otp@test.com',
            'otp_code' => '123456',
            'otp_expires_at' => now()->subMinute() // Expired
        ]);

        session()->put('email_for_password_reset', 'otp@test.com');

        $response = $this->post(route('forgot_otp.verify'), [
            'otp' => '123456'
        ]);

        $response->assertSessionHas('swal_error_crud', 'Kode OTP salah atau telah kedaluwarsa.');
    }

    #[Test]
    public function otp_resend_berhasil_mengirim_ulang()
    {
        Mail::fake();
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['email' => 'otp@test.com']);
        session()->put('email_for_password_reset', 'otp@test.com');

        $response = $this->post(route('forgot_otp.resend'));

        $response->assertRedirect(); // Back
        $response->assertSessionHas('swal_success_crud', 'Kode OTP baru telah dikirim ke email Anda.');
        
        $user->refresh();
        $this->assertNotNull($user->otp_code);
        Mail::assertSent(SendOtpMail::class);
    }

    // =========================================================================
    // 🟡 RESET PASSWORD (FORM & ACTION)
    // =========================================================================

    #[Test]
    public function reset_form_redirect_jika_sesi_invalid()
    {
        // Kasus 1: Tidak ada email di sesi
        $response = $this->get(route('reset_pass.form'));
        $response->assertRedirect(route('forgot.form'));

        // Kasus 2: Ada email, tapi OTP belum verified
        session()->put('email_for_password_reset', 'test@test.com');
        $response = $this->get(route('reset_pass.form'));
        $response->assertRedirect(route('forgot.form'));
    }

    #[Test]
    public function reset_password_sukses_update_db_dan_clear_session()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'reset@test.com',
            'password' => Hash::make('OldPassword'),
            'otp_code' => '123456',
            'locked_until' => now()->addHour() // Simulasi sedang terlock
        ]);

        session()->put('email_for_password_reset', 'reset@test.com');
        session()->put('otp_verified', true);

        $newPassword = 'NewPassword123!';

        $response = $this->post(route('reset_pass.attempt'), [
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertRedirect(route('login.form'));
        $response->assertSessionHas('swal_success_crud', 'Password berhasil direset! Silakan login.');

        // Verifikasi DB
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertNull($user->otp_code);
        $this->assertNull($user->locked_until); // Lock harus direset
        $this->assertEquals(0, $user->failed_attempts);

        // Verifikasi Session dihapus
        $this->assertFalse(session()->has('email_for_password_reset'));
        $this->assertFalse(session()->has('otp_verified'));
    }

    #[Test]
    public function reset_password_gagal_jika_password_baru_sama_dengan_lama()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'reset@test.com',
            'password' => Hash::make('OldPassword123!')
        ]);

        session()->put('email_for_password_reset', 'reset@test.com');
        session()->put('otp_verified', true);

        $response = $this->post(route('reset_pass.attempt'), [
            'password' => 'OldPassword123!',
            'password_confirmation' => 'OldPassword123!'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('swal_error_crud', 'Password baru tidak boleh sama dengan password lama.');
    }

    #[Test]
    public function reset_password_menangani_exception()
    {
        session()->put('email_for_password_reset', 'test@test.com');
        session()->put('otp_verified', true);

        DB::shouldReceive('transaction')->andThrow(new \Exception('Server Error'));

        $response = $this->post(route('reset_pass.attempt'), [
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('swal_error_crud', 'Terjadi kesalahan pada server.');
    }
}