<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MissionControllerTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/missions');
        $response->assertStatus(200)->assertViewIs('Contents.Missions.index');
    }

    #[Test]
    public function store_creates_mission()
    {
        $response = $this->actingAs($this->admin)->from('/admin/missions')->post('/admin/missions', [
            'mission_title' => 'Test Mission',
            'mission_description' => 'Desc',
            'mission_points' => 10,
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_reset_cycle' => 'DAILY',
            'mission_threshold' => 1,
            'mission_is_consecutive' => 0,
            'mission_is_active' => 1,
        ]);
        $response->assertRedirect('/admin/missions')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('missions', ['mission_title' => 'Test Mission']);
    }

    #[Test]
    public function update_modifies_mission()
    {
        $mission = Mission::create([
            'mission_title' => 'Old', 
            'mission_description' => 'Desc', 
            'mission_points' => 10,
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_reset_cycle' => 'DAILY',
            'mission_threshold' => 1,
            'mission_is_consecutive' => 0,
            'mission_is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->from('/admin/missions')->put("/admin/missions/{$mission->mission_id}", [
            'mission_title' => 'New',
            'mission_description' => 'Desc',
            'mission_points' => 20,
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_reset_cycle' => 'DAILY',
            'mission_threshold' => 1,
            'mission_is_consecutive' => 0,
            'mission_is_active' => 1,
        ]);
        
        $response->assertRedirect('/admin/missions')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('missions', ['mission_title' => 'New']);
    }

    #[Test]
    public function destroy_deletes_mission()
    {
        $mission = Mission::create([
            'mission_title' => 'To Delete', 
            'mission_description' => 'Desc', 
            'mission_points' => 10,
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_reset_cycle' => 'DAILY',
            'mission_threshold' => 1,
            'mission_is_consecutive' => 0,
            'mission_is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->from('/admin/missions')->delete("/admin/missions/{$mission->mission_id}");
        $response->assertRedirect('/admin/missions')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseMissing('missions', ['mission_id' => $mission->mission_id]);
    }
}
