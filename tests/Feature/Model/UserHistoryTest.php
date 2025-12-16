<?php

namespace Tests\Feature\Models;

use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserHistoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_history_dapat_ditambahkan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $historyData = [
            'user_id' => $user->user_id,
            'user_history_type' => UserHistory::TYPE_MISSION,
            'user_history_name' => 'Misi Selesai',
            'user_history_points' => 100,
            'user_history_is_negative' => false,
        ];

        $history = UserHistory::create($historyData);

        $this->assertDatabaseHas('user_histories', [
            'user_id' => $user->user_id,
            'user_history_name' => 'Misi Selesai'
        ]);
        $this->assertEquals(100, $history->user_history_points);
    }

    #[Test]
    public function user_history_dapat_didapatkan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $history = UserHistory::create([
            'user_id' => $user->user_id,
            'user_history_type' => 'redeem',
            'user_history_name' => 'Tukar Poin',
            'user_history_points' => 50,
            'user_history_is_negative' => true,
        ]);

        $foundHistory = UserHistory::find($history->user_history_id);

        $this->assertNotNull($foundHistory);
        $this->assertEquals('Tukar Poin', $foundHistory->user_history_name);
    }

    #[Test]
    public function user_history_dapat_diubah()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $history = UserHistory::create([
            'user_id' => $user->user_id,
            'user_history_type' => 'mission',
            'user_history_name' => 'Salah Nama',
            'user_history_points' => 10,
        ]);

        $history->update([
            'user_history_name' => 'Nama Benar'
        ]);

        $this->assertDatabaseHas('user_histories', [
            'user_history_id' => $history->user_history_id,
            'user_history_name' => 'Nama Benar'
        ]);
    }

    #[Test]
    public function user_history_dapat_dihapus()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $history = UserHistory::create([
            'user_id' => $user->user_id,
            'user_history_type' => 'mission',
            'user_history_name' => 'To Delete',
            'user_history_points' => 10,
        ]);

        $history->delete();

        $this->assertDatabaseMissing('user_histories', [
            'user_history_id' => $history->user_history_id,
        ]);
    }
}