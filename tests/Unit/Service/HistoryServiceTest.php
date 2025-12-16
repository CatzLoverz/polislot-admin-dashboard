<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserHistory;
use App\Services\HistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $historyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->historyService = new HistoryService();
    }

    #[Test]
    public function log_mencatat_history_ke_database()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->historyService->log(
            $user->user_id,
            UserHistory::TYPE_MISSION,
            'Misi Harian Selesai',
            100,
            false
        );

        $this->assertDatabaseHas('user_histories', [
            'user_id' => $user->user_id,
            'user_history_type' => UserHistory::TYPE_MISSION,
            'user_history_name' => 'Misi Harian Selesai',
            'user_history_points' => 100,
            'user_history_is_negative' => false
        ]);
    }

    #[Test]
    public function log_mencatat_history_negatif_dengan_benar()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->historyService->log(
            $user->user_id,
            UserHistory::TYPE_REDEEM,
            'Tukar Voucher',
            50,
            true // isNegative
        );

        $this->assertDatabaseHas('user_histories', [
            'user_id' => $user->user_id,
            'user_history_is_negative' => true,
            'user_history_points' => 50
        ]);
    }

    #[Test]
    public function log_menangani_exception_tanpa_menghentikan_aplikasi()
    {
        // Simulasi ID user yang tidak valid akan menyebabkan QueryException (Foreign Key Constraint)
        // Service harus menangkap exception tersebut di block catch dan melog error (bukan throw exception)
        
        $nonExistentUserId = 99999; 
        
        // Pastikan tidak ada exception yang dilempar keluar
        try {
            $this->historyService->log(
                $nonExistentUserId,
                'error_test',
                'Test Error',
                10
            );
            $this->assertTrue(true); // Berhasil melewati tanpa crash
        } catch (\Exception $e) {
            $this->fail('Service seharusnya menangkap exception: ' . $e->getMessage());
        }

        // Pastikan tidak ada data masuk
        $this->assertDatabaseMissing('user_histories', ['user_id' => $nonExistentUserId]);
    }
}