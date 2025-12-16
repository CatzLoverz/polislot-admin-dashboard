<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function data_user_dapat_ditambahkan()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'user',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    #[Test]
    public function data_user_dapat_didapatkan()
    {
        $user = User::factory()->create();

        $foundUser = User::find($user->user_id);

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->email, $foundUser->email);
    }

    #[Test]
    public function data_user_dapat_diubah()
    {
        $user = User::factory()->create([
            'name' => 'Old Name'
        ]);

        $user->update([
            'name' => 'New Name'
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'name' => 'New Name'
        ]);
    }

    #[Test]
    public function data_user_dapat_dihapus()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'user_id' => $user->user_id,
        ]);
    }
}