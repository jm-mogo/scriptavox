<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizePassage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'chapter',
        'verse_from',
        'verse_to',
        'hide_level',
    ];

    protected $casts = [
        'chapter' => 'integer',
        'verse_from' => 'integer',
        'verse_to' => 'integer',
        'hide_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
