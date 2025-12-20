<?php

namespace Tests\Feature\Models;

use App\Models\InfoBoard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InfoBoardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function infoboard_dapat_dibuat()
    {
        $user = \App\Models\User::factory()->create();
        $info = InfoBoard::create([
            'user_id' => $user->user_id,
            'info_title' => 'Pengumuman Penting',
            'info_content' => 'Area parkir A tutup sementara.'
        ]);

        $this->assertDatabaseHas('info_boards', [
            'info_id' => $info->info_id,
            'info_title' => 'Pengumuman Penting'
        ]);
    }

    #[Test]
    public function infoboard_dapat_dibaca()
    {
        $user = \App\Models\User::factory()->create();
        $info = InfoBoard::create([
            'user_id' => $user->user_id,
            'info_title' => 'Read Me',
            'info_content' => 'Content'
        ]);

        $found = InfoBoard::find($info->info_id);
        
        $this->assertNotNull($found);
        $this->assertEquals('Read Me', $found->info_title);
    }

    #[Test]
    public function infoboard_dapat_diupdate()
    {
        $user = \App\Models\User::factory()->create();
        $info = InfoBoard::create([
            'user_id' => $user->user_id,
            'info_title' => 'Judul Lama',
            'info_content' => '...'
        ]);

        $info->update(['info_title' => 'Judul Baru']);

        $this->assertDatabaseHas('info_boards', [
            'info_id' => $info->info_id,
            'info_title' => 'Judul Baru'
        ]);
    }

    #[Test]
    public function infoboard_dapat_dihapus()
    {
        $user = \App\Models\User::factory()->create();
        $info = InfoBoard::create([
            'user_id' => $user->user_id,
            'info_title' => 'Hapus Saya',
            'info_content' => '...'
        ]);

        $info->delete();

        $this->assertDatabaseMissing('info_boards', ['info_id' => $info->info_id]);
    }
}
