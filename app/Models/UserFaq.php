<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class UserFaq extends Model
{
    protected $table = 'user_faq';
    protected $primaryKey = 'faq_id';
    public $timestamps = true;
 
    protected $fillable = [
        'user_id',
        'faq_question',
        'faq_answer',
    ];
 
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
 