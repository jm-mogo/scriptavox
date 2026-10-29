<?php

use App\Enums\Testament;
use App\Models\Book;
use App\Models\MemorizePassage;
use App\Models\User;
use App\Models\Verse;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->book = Book::create([
        'title' => 'Juan',
        'abbreviation' => 'Jn',
        'testament' => Testament::NEW_TESTAMENT,
        'book_number' => 43,
    ]);

    foreach ([1, 2] as $chapter) {
        Verse::create([
            'book_id' => $this->book->id,
            'chapter' => $chapter,
            'verse_number' => 1,
            'text' => "Capítulo {$chapter}",
        ]);
    }

    $this->user = User::factory()->create();
});

test('the start screen lists books with their chapter counts', function () {
    $this->actingAs($this->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('books', 1)
            ->where('books.0.title', 'Juan')
            ->where('books.0.chapter_count', 2));
});

test('the start screen shows the user\'s saved passages', function () {
    MemorizePassage::create([
        'user_id' => $this->user->id, 'book_id' => $this->book->id,
        'chapter' => 1, 'verse_from' => 1, 'verse_to' => 1, 'hide_level' => 25,
    ]);
    MemorizePassage::create([
        'user_id' => User::factory()->create()->id, 'book_id' => $this->book->id,
        'chapter' => 2, 'verse_from' => 1, 'verse_to' => 1, 'hide_level' => 0,
    ]);

    $this->actingAs($this->user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->has('passages', 1)
            ->where('passages.0.hide_level', 25));
});
