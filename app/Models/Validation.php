<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    protected $table = 'validations';
    protected $primaryKey = 'validation_id';
    
    protected $fillable = ['validation_points'];
}