<?php

use App\Enums\Testament;
use App\Models\Book;
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

    foreach ([1, 2, 3] as $number) {
        Verse::create([
            'book_id' => $this->book->id,
            'chapter' => 3,
            'verse_number' => $number,
            'text' => "Versículo {$number} de prueba",
        ]);
    }
});

test('guests are redirected from the memorize chapter page', function () {
    $this->get("/memorize/{$this->book->id}/3")->assertRedirect('/login');
});

test('authenticated users see every verse of the chapter in order', function () {
    $this->actingAs(User::factory()->create());

    $this->get("/memorize/{$this->book->id}/3")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Memorize/Chapter')
            ->where('chapter', 3)
            ->has('verses', 3)
            ->where('verses.0.verse_number', 1)
            ->where('verses.2.verse_number', 3));
});

test('a chapter with no verses returns an empty list', function () {
    $this->actingAs(User::factory()->create());

    $this->get("/memorize/{$this->book->id}/99")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('verses', 0));
});
