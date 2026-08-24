<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Mail\SendOtpMail;
use App\Models\User;
use App\Services\MissionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware; // Bypass middleware encryptApi

    // =========================================================================
    // 🟡 AUTH CHECK
    // =========================================================================

    #[Test]
    public function auth_check_returns_200_and_triggers_mission_if_cache_empty()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Mock MissionService: Harus dipanggil karena cache kosong
        $this->mock(MissionService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('updateProgress')
                ->once()
                ->with($user->user_id, 'LOGIN_ACTION');
        });

        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data profil berhasil diambil.',
            ]);

        // Pastikan cache tersimpan
        $cacheKey = 'daily_login_'.$user->user_id.'_'.now()->format('Y-m-d');
        $this->assertTrue(Cache::has($cacheKey));
    }

    #[Test]
    public function auth_check_returns_200_but_no_mission_if_cache_exists()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Set Cache seolah-olah sudah login hari ini
        $cacheKey = 'daily_login_'.$user->user_id.'_'.now()->format('Y-m-d');
        Cache::put($cacheKey, true, now()->endOfDay());

        // Mock MissionService: TIDAK boleh dipanggil
        $this->mock(MissionService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('updateProgress');
        });

        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
    }

    #[Test]
    public function auth_check_returns_200_even_if_service_fails()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Mock Exception
        $this->mock(MissionService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateProgress')->andThrow(new Exception('Service Down'));
        });

        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        // User tidak boleh error 500, hanya log error (internal) dan return data user
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    // =========================================================================
    // 🟡 REGISTER
    // =========================================================================

    #[Test]
    public function register_attempt_returns_201_for_new_user()
    {
        Mail::fake();
        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register-attempt', $data);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);

        // User BELUM tercipta di DB — hanya tersimpan di cache
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
        $this->assertTrue(Cache::has('reg_pending_new@example.com'));
        Mail::assertSent(SendOtpMail::class);
    }

    #[Test]
    public function register_attempt_returns_201_overwriting_unverified_user()
    {
        Mail::fake();
        // Payload cache lama — simulasikan pendaftaran sebelumnya yang belum diverifikasi
        Cache::put('reg_pending_duplicate@example.com', [
            'name' => 'Old Owner',
            'email' => 'duplicate@example.com',
            'password' => Hash::make('OldPass123!'),
            'otp_hash' => hash('sha256', '000000'),
        ], now()->addMinutes(10));

        $data = [
            'name' => 'New Owner',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register-attempt', $data);

        $response->assertStatus(201);

        // Payload cache sudah tertimpa
        $payload = Cache::get('reg_pending_duplicate@example.com');
        $this->assertEquals('New Owner', $payload['name']);
        $this->assertNotEquals(hash('sha256', '000000'), $payload['otp_hash']);
    }

    #[Test]
    public function register_attempt_returns_422_if_validation_fails()
    {
        $response = $this->postJson('/api/register-attempt', [
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function register_attempt_returns_500_on_system_error()
    {
        Cache::shouldReceive('put')->andThrow(new Exception('Cache Error'));

        $response = $this->postJson('/api/register-attempt', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'Pass123!',
            'password_confirmation' => 'Pass123!',
        ]);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Terjadi kesalahan pada server.']);
    }

    // =========================================================================
    // 🟡 REGISTER OTP VERIFY
    // =========================================================================

    #[Test]
    public function register_otp_verify_returns_200_on_success()
    {
        Cache::put('reg_pending_new@example.com', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => Hash::make('Password123!'),
            'otp_hash' => hash('sha256', '123456'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/register-otp-verify', [
            'email' => 'new@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token']]);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $user = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse(Cache::has('reg_pending_new@example.com'));
    }

    #[Test]
    public function register_otp_verify_returns_400_if_already_verified()
    {
        /** @var User $user */
        $user = User::factory()->create(); // Verified by default

        $response = $this->postJson('/api/register-otp-verify', [
            'email' => $user->email,
            'otp' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Email ini sudah terverifikasi.']);
    }

    #[Test]
    public function register_otp_verify_returns_422_if_otp_invalid_or_expired()
    {
        // Cache kosong simulasikan sesi sudah expired
        $response = $this->postJson('/api/register-otp-verify', [
            'email' => 'ghost@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Kode OTP salah atau telah kedaluwarsa.']);

        // OTP salah
        Cache::put('reg_pending_wrong@example.com', [
            'name' => 'Wrong OTP',
            'email' => 'wrong@example.com',
            'password' => Hash::make('Password123!'),
            'otp_hash' => hash('sha256', '123456'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/register-otp-verify', [
            'email' => 'wrong@example.com',
            'otp' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Kode OTP salah atau telah kedaluwarsa.']);
    }

    // =========================================================================
    // 🟡 REGISTER OTP RESEND
    // =========================================================================

    #[Test]
    public function register_otp_resend_returns_200_on_success()
    {
        Mail::fake();
        Cache::put('reg_pending_resend@example.com', [
            'name' => 'Resend User',
            'email' => 'resend@example.com',
            'password' => Hash::make('Password123!'),
            'otp_hash' => hash('sha256', '000000'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/register-otp-resend', [
            'email' => 'resend@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // OTP hash di cache harus berubah
        $payload = Cache::get('reg_pending_resend@example.com');
        $this->assertNotEquals(hash('sha256', '000000'), $payload['otp_hash']);
        Mail::assertSent(SendOtpMail::class);
    }

    #[Test]
    public function register_otp_resend_returns_400_if_already_verified()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/register-otp-resend', [
            'email' => $user->email,
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Email sudah terverifikasi.']);
    }

    #[Test]
    public function register_otp_resend_returns_422_if_email_missing()
    {
        // Tidak ada payload cache → sesi registrasi tidak ditemukan
        $response = $this->postJson('/api/register-otp-resend', [
            'email' => 'ghost@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Sesi registrasi tidak ditemukan, silakan daftar ulang.']);
    }

    // =========================================================================
    // 🟡 LOGIN
    // =========================================================================

    #[Test]
    public function login_attempt_returns_200_and_resets_failures()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('Correct!'),
            'failed_attempts' => 2,
            'locked_until' => null,
        ]);

        Mail::fake();

        $response = $this->postJson('/api/login-attempt', [
            'email' => $user->email,
            'password' => 'Correct!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $user->refresh();
        $this->assertEquals(0, $user->failed_attempts); // Harus direset
    }

    #[Test]
    public function login_attempt_resets_failures_automatically_if_time_passed()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('Correct!'),
            'failed_attempts' => 2,
            'updated_at' => now()->subMinutes(15), // Lebih dari 10 menit lalu
        ]);

        // Coba login salah pun, dia akan reset dulu logicnya karena > 10 menit
        // Tapi karena password salah, attempt jadi 1.
        $response = $this->postJson('/api/login-attempt', [
            'email' => $user->email,
            'password' => 'Wrong!',
        ]);

        $user->refresh();
        // Awalnya 2 -> Reset jadi 0 (karena expired) -> Tambah 1 (karena salah) = 1
        $this->assertEquals(1, $user->failed_attempts);
    }

    #[Test]
    public function login_attempt_returns_401_if_email_not_found()
    {
        $response = $this->postJson('/api/login-attempt', [
            'email' => 'missing@example.com',
            'password' => 'pass',
        ]);
        $response->assertStatus(401);
    }

    #[Test]
    public function login_attempt_returns_403_if_unverified()
    {
        /** @var User $user */
        $user = User::factory()->unverified()->create([
            'password' => Hash::make('Pass'),
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/login-attempt', [
            'email' => $user->email,
            'password' => 'Pass',
        ]);
        $response->assertStatus(403)->assertJson(['code' => 'UNVERIFIED']);
    }

    #[Test]
    public function login_attempt_returns_401_if_locked()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/login-attempt', [
            'email' => $user->email,
            'password' => 'Any',
        ]);
        $response->assertStatus(401);
    }

    #[Test]
    public function login_attempt_returns_401_and_locks_after_4_failures()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('Correct!'),
            'failed_attempts' => 3,
        ]);

        $response = $this->postJson('/api/login-attempt', [
            'email' => $user->email,
            'password' => 'Wrong!',
        ]);

        $response->assertStatus(401);
        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertEquals(0, $user->failed_attempts); // Reset setelah lock
    }

    // =========================================================================
    // 🟡 LOGOUT
    // =========================================================================

    #[Test]
    public function logout_returns_200_on_success()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/logout');
        $response->assertStatus(200);
    }

    #[Test]
    public function logout_returns_401_if_token_invalid()
    {
        // Tanpa actingAs / Token
        $response = $this->postJson('/api/logout');
        // Middleware sanctum biasanya reject 401.
        // Jika bypass middleware auth di test, controller cek $request->user(), null -> 401.
        $response->assertStatus(401);
    }

    // =========================================================================
    // 🟡 FORGOT PASSWORD (Kirim OTP)
    // =========================================================================

    #[Test]
    public function forgot_attempt_returns_200_on_success()
    {
        Mail::fake();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-attempt', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // OTP tersimpan di cache (bukan di DB)
        $this->assertTrue(Cache::has('forgot_pass_' . Str::lower($user->email)));
        Mail::assertSent(SendOtpMail::class);
    }

    #[Test]
    public function forgot_attempt_returns_422_if_email_not_found()
    {
        $response = $this->postJson('/api/forgot-attempt', [
            'email' => 'nohere@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Email tidak ditemukan.']);
    }

    // =========================================================================
    // 🟡 FORGOT OTP VERIFY
    // =========================================================================

    #[Test]
    public function forgot_otp_verify_returns_200_on_success()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Simpan OTP yang valid di cache
        Cache::put('forgot_pass_' . Str::lower($user->email), [
            'otp_hash' => hash('sha256', '654321'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/forgot-otp-verify', [
            'email' => $user->email,
            'otp' => '654321',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'OTP valid. Silakan reset password.']);
    }

    #[Test]
    public function forgot_otp_verify_returns_400_if_invalid_or_expired()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Test 1: Cache kosong (expired)
        $response = $this->postJson('/api/forgot-otp-verify', [
            'email' => $user->email,
            'otp' => '654321',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Kode OTP salah atau telah kedaluwarsa.']);

        // Test 2: OTP salah
        Cache::put('forgot_pass_' . Str::lower($user->email), [
            'otp_hash' => hash('sha256', '123456'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/forgot-otp-verify', [
            'email' => $user->email,
            'otp' => '999999',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Kode OTP salah atau telah kedaluwarsa.']);
    }

    // =========================================================================
    // 🟡 FORGOT OTP RESEND
    // =========================================================================

    #[Test]
    public function forgot_otp_resend_returns_200_on_success()
    {
        Mail::fake();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-otp-resend', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        Mail::assertSent(SendOtpMail::class);
    }

    #[Test]
    public function forgot_otp_resend_returns_500_on_system_error()
    {
        Cache::shouldReceive('put')->andThrow(new Exception('Error'));
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-otp-resend', [
            'email' => $user->email,
        ]);

        $response->assertStatus(500);
    }

    // =========================================================================
    // 🟡 RESET PASSWORD
    // =========================================================================

    #[Test]
    public function reset_pass_attempt_returns_200_on_success()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!'),
            'locked_until' => now()->addHour(), // Test clear lock
        ]);

        $emailLower = Str::lower($user->email);
        Cache::put("forgot_pass_{$emailLower}", [
            'otp_hash' => hash('sha256', '123456'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/reset-pass-attempt', [
            'email' => $user->email,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
            'token' => '123456',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $user->password));
        $this->assertNull($user->locked_until);

        // Pastikan cache sudah dibersihkan
        $this->assertFalse(Cache::has("forgot_pass_{$emailLower}"));
    }

    #[Test]
    public function reset_pass_attempt_returns_400_if_password_same_as_old()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!'),
        ]);

        $emailLower = Str::lower($user->email);
        Cache::put("forgot_pass_{$emailLower}", [
            'otp_hash' => hash('sha256', '123456'),
        ], now()->addMinutes(10));

        $response = $this->postJson('/api/reset-pass-attempt', [
            'email' => $user->email,
            'password' => 'OldPass123!',
            'password_confirmation' => 'OldPass123!',
            'token' => '123456',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Password baru tidak boleh sama dengan yang lama.']);
    }

    #[Test]
    public function reset_pass_attempt_returns_422_if_validation_fails()
    {
        $response = $this->postJson('/api/reset-pass-attempt', [
            'email' => 'mail@mail.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $response->assertStatus(422);
    }
}
