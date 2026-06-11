<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserValidation extends Model
{
    protected $table = 'user_validations';
    protected $primaryKey = 'user_validation_id';
    
    protected $fillable = [
        'user_id',
        'validation_id',
        'park_subarea_id',
        'user_validation_content' // 'banyak', 'terbatas', 'penuh'
    ];

    /**
     * Relasi ke pengguna yang melakukan validasi.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke subarea parkir yang divalidasi.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parkSubarea(): BelongsTo
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }
}