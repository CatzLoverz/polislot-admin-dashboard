<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\InfoBoard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InfoBoardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function index_returns_view()
    {
        $response = $this->actingAs($this->admin)->get('/admin/info-board');
        $response->assertStatus(200)->assertViewIs('Contents.InfoBoard.index');
    }

    #[Test]
    public function store_creates_info_board()
    {
        $response = $this->actingAs($this->admin)->post('/admin/info-board', [
            'info_title' => 'Title',
            'info_content' => 'Desc',
        ]);
        $response->assertRedirect('/admin/info-board')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('info_boards', ['info_title' => 'Title', 'user_id' => $this->admin->user_id]);
    }

    #[Test]
    public function update_modifies_info_board()
    {
        $info = InfoBoard::create(['info_title' => 'Old', 'info_content' => 'Desc', 'user_id' => $this->admin->user_id]);

        $response = $this->actingAs($this->admin)->put("/admin/info-board/{$info->info_id}", [
            'info_title' => 'New',
            'info_content' => 'Desc',
        ]);

        $response->assertRedirect('/admin/info-board')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('info_boards', ['info_title' => 'New']);
    }

    #[Test]
    public function destroy_deletes_info_board()
    {
        $info = InfoBoard::create(['info_title' => 'To Delete', 'info_content' => 'Desc', 'user_id' => $this->admin->user_id]);

        $response = $this->actingAs($this->admin)->delete("/admin/info-board/{$info->info_id}");
        $response->assertRedirect('/admin/info-board')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseMissing('info_boards', ['info_id' => $info->info_id]);
    }
}
