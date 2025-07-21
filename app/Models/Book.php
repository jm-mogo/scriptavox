<?php

namespace App\Models;

use App\Enums\Testament; // Import the Enum we just created
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Verse;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This is a security feature to prevent unintended fields from being updated.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_number',
        'title',
        'abbreviation',
        'testament',
    ];

    /**
     * The attributes that should be cast to native types.
     * This ensures data consistency (e.g., testament is always a Testament enum).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'testament' => Testament::class,
    ];

    /**
     * Defines the one-to-many relationship: a Book has many Verses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verses(): HasMany
    {
        return $this->hasMany(Verse::class);
    }
}
