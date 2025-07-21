<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Verse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'chapter',
        'verse_number',
        'text',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'book_id' => 'integer',
        'chapter' => 'integer',
        'verse_number' => 'integer',
    ];

    /**
     * Defines the inverse of the one-to-many relationship: a Verse belongs to a Book.
     * This allows you to easily get the book details from a verse instance (e.g., $verse->book->title).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
