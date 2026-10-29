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

    foreach (range(1, 5) as $number) {
        Verse::create([
            'book_id' => $this->book->id,
            'chapter' => 3,
            'verse_number' => $number,
            'text' => "Versículo {$number} de prueba",
        ]);
    }

    $this->user = User::factory()->create();
});

test('a user can save a range of verses as a passage', function () {
    $this->actingAs($this->user)
        ->post('/passages', [
            'book_id' => $this->book->id,
            'chapter' => 3,
            'verse_from' => 2,
            'verse_to' => 4,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('memorize_passages', [
        'user_id' => $this->user->id,
        'book_id' => $this->book->id,
        'chapter' => 3,
        'verse_from' => 2,
        'verse_to' => 4,
        'hide_level' => 0,
    ]);
});

test('a passage cannot start after it ends', function () {
    $this->actingAs($this->user)
        ->post('/passages', [
            'book_id' => $this->book->id,
            'chapter' => 3,
            'verse_from' => 4,
            'verse_to' => 2,
        ])
        ->assertSessionHasErrors('verse_to');

    $this->assertDatabaseCount('memorize_passages', 0);
});

test('a user sees only their own passages on the list', function () {
    MemorizePassage::create([
        'user_id' => $this->user->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 1, 'verse_to' => 2, 'hide_level' => 0,
    ]);
    MemorizePassage::create([
        'user_id' => User::factory()->create()->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 1, 'verse_to' => 5, 'hide_level' => 0,
    ]);

    $this->actingAs($this->user)
        ->get('/passages')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Passages/Index')
            ->has('passages', 1)
            ->where('passages.0.verse_to', 2));
});

test('a saved passage resumes with its hide level and verses in range', function () {
    $passage = MemorizePassage::create([
        'user_id' => $this->user->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 2, 'verse_to' => 4, 'hide_level' => 50,
    ]);

    $this->actingAs($this->user)
        ->get("/passages/{$passage->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Passages/Show')
            ->where('passage.hide_level', 50)
            ->has('verses', 3)
            ->where('verses.0.verse_number', 2)
            ->where('verses.2.verse_number', 4));
});

test('updating the hide level is saved for the passage', function () {
    $passage = MemorizePassage::create([
        'user_id' => $this->user->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 1, 'verse_to' => 2, 'hide_level' => 0,
    ]);

    $this->actingAs($this->user)
        ->patch("/passages/{$passage->id}", ['hide_level' => 75])
        ->assertRedirect();

    expect($passage->fresh()->hide_level)->toBe(75);
});

test('a user cannot open or change another user\'s passage', function () {
    $other = MemorizePassage::create([
        'user_id' => User::factory()->create()->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 1, 'verse_to' => 2, 'hide_level' => 0,
    ]);

    $this->actingAs($this->user)->get("/passages/{$other->id}")->assertNotFound();
    $this->actingAs($this->user)->patch("/passages/{$other->id}", ['hide_level' => 100])->assertNotFound();
    expect($other->fresh()->hide_level)->toBe(0);
});

test('the 100% hidden level is not allowed', function () {
    $passage = MemorizePassage::create([
        'user_id' => $this->user->id, 'book_id' => $this->book->id,
        'chapter' => 3, 'verse_from' => 1, 'verse_to' => 2, 'hide_level' => 0,
    ]);

    $this->actingAs($this->user)
        ->patch("/passages/{$passage->id}", ['hide_level' => 100])
        ->assertSessionHasErrors('hide_level');

    expect($passage->fresh()->hide_level)->toBe(0);
});

test('the passages page offers books with their chapter and verse counts', function () {
    $this->actingAs($this->user)
        ->get('/passages')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Passages/Index')
            ->has('books', 1)
            ->where('books.0.id', $this->book->id)
            ->where('books.0.chapter_count', 3)
            ->where('books.0.verses.3', 5));
});
