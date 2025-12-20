<?php

namespace Tests\Feature\Models;

use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function validation_setting_dapat_dibuat()
    {
        $v = Validation::create([
            'validation_points' => 50
        ]);

        $this->assertDatabaseHas('validations', [
            'validation_points' => 50
        ]);
    }


    #[Test]
    public function validation_setting_dapat_dibaca()
    {
        $v = Validation::create(['validation_points' => 50]);
        $found = Validation::find($v->validation_id);
        
        $this->assertNotNull($found);
        $this->assertEquals(50, $found->validation_points);
    }

    #[Test]
    public function validation_setting_dapat_diupdate()
    {
        $v = Validation::create(['validation_points' => 50]);
        $v->update(['validation_points' => 100]);

        $this->assertDatabaseHas('validations', [
            'validation_id' => $v->validation_id,
            'validation_points' => 100
        ]);
    }

    #[Test]
    public function validation_setting_dapat_dihapus()
    {
        $v = Validation::create(['validation_points' => 50]);
        $v->delete();

        $this->assertDatabaseMissing('validations', ['validation_id' => $v->validation_id]);
    }
}
