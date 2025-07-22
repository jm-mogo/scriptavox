<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerseProgress extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * We specify this because the class name is long, and Laravel's
     * default table name would be 'user_verse_progresses', which is awkward.
     *
     * @var string
     */
    protected $table = 'user_verse_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verse_id',
        'status',
        'review_at',
        'interval',
        'ease_factor',
        'lesson_id', // Make sure this is in your migration and nullable
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'review_at' => 'datetime',
        'ease_factor' => 'float',
    ];

    /**
     * Get the user that owns this progress record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verse associated with this progress record.
     */
    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }
}
