<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
use App\Http\Middleware\RBAC; // Import Middleware RBAC
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 🟢 SOLUSI UTAMA: Disable Middleware RBAC untuk test file ini saja.
        // Ini mencegah RBAC mengganti koneksi database yang menyebabkan hang/deadlock
        // saat berjalan bersamaan dengan RefreshDatabase.
        $this->withoutMiddleware([RBAC::class]);
    }

    // =========================================================================
    // 🟡 EDIT FORM
    // =========================================================================

    #[Test]
    public function edit_profile_halaman_dapat_diakses()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.Profile.index');
        $response->assertViewHas('user');
    }

    // =========================================================================
    // 🟡 UPDATE PROFILE (BASIC & AVATAR)
    // =========================================================================

    #[Test]
    public function update_profile_nama_dan_avatar_berhasil()
    {
        Storage::fake('public');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => 'Old Name',
            'avatar' => 'default_avatar.jpg'
        ]);

        $file = UploadedFile::fake()->image('new_avatar.jpg');

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'avatar' => $file
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('swal_success_crud', 'Profil Anda berhasil diperbarui.');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertNotEquals('default_avatar.jpg', $user->avatar);
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $fs */
        $fs = Storage::disk('public');
        $fs->assertExists($user->avatar);
    }

    #[Test]
    public function update_profile_menghapus_avatar_lama_jika_bukan_default()
    {
        Storage::fake('public');

        $oldAvatar = UploadedFile::fake()->image('old.jpg');
        /** @var \Illuminate\Filesystem\FilesystemAdapter $fs */
        $fs = Storage::disk('public');
        $path = $fs->putFile('avatars', $oldAvatar);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'avatar' => $path
        ]);

        $newAvatar = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'avatar' => $newAvatar
            ]);

        $fs->assertMissing($path);
        
        $user->refresh();
        $fs->assertExists($user->avatar);
    }

    // =========================================================================
    // 🟡 UPDATE PASSWORD
    // =========================================================================

    #[Test]
    public function update_password_berhasil()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!')
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'current_password' => 'CurrentPass123!',
                'new_password' => 'NewPass456!',
                'new_password_confirmation' => 'NewPass456!',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('swal_success_crud');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass456!', $user->password));
    }

    #[Test]
    public function update_password_gagal_jika_password_lama_salah()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass123!')
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'current_password' => 'WrongPass!',
                'new_password' => 'NewPass456!',
                'new_password_confirmation' => 'NewPass456!',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['current_password']);
        
        $this->assertTrue(Hash::check('CurrentPass123!', $user->refresh()->password));
    }

    #[Test]
    public function update_password_gagal_jika_password_baru_sama_dengan_lama()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'password' => Hash::make('SamePass123!')
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'current_password' => 'SamePass123!',
                'new_password' => 'SamePass123!',
                'new_password_confirmation' => 'SamePass123!',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['new_password']);
    }

    // =========================================================================
    // 🟡 EXCEPTION HANDLING & ROLLBACK
    // =========================================================================

    #[Test]
    public function update_profile_menangani_exception_sistem_dan_rollback()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => 'Original Name',
            'password' => Hash::make('OldPass') // Password asli di DB
        ]);

        // 🟢 SMART MOCKING:
        // Kita atur logika Hash::check agar lolos kedua validasi yang bertolak belakang.
        Hash::shouldReceive('check')
            ->andReturnUsing(function ($inputPassword, $hashedPassword) {
                // 1. Validasi 'current_password': Input 'OldPass' harus dianggap COCOK (True)
                if ($inputPassword === 'OldPass') {
                    return true;
                }
                // 2. Validasi 'NotCurrentPassword': Input 'NewPass123!' harus dianggap BEDA (False)
                return false; 
            });

        // Simulasi Error Sistem saat proses hashing password baru
        Hash::shouldReceive('make')
            ->once()
            ->with('NewPass123!')
            ->andThrow(new \Exception('Simulated Hashing Error'));

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => 'New Name',
                'current_password' => 'OldPass',
                'new_password' => 'NewPass123!',
                'new_password_confirmation' => 'NewPass123!',
            ]);

        $response->assertRedirect(route('profile.edit'));
        
        // Assert pesan error sistem muncul (bukan error validasi)
        $response->assertSessionHas('swal_error_crud', 'Terjadi kesalahan pada server saat memperbarui profil.');

        // Assert Rollback Berjalan: Nama user HARUS tetap 'Original Name'
        $this->assertEquals('Original Name', $user->refresh()->name);
    }
}