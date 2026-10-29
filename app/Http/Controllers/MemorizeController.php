<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Verse;
use Inertia\Inertia;
use Inertia\Response;

class MemorizeController extends Controller
{
    /**
     * Show every verse of a chapter for memorizing, in order.
     */
    public function chapter(Book $book, int $chapter): Response
    {
        $verses = Verse::where('book_id', $book->id)
            ->where('chapter', $chapter)
            ->orderBy('verse_number')
            ->get(['id', 'verse_number', 'text']);

        return Inertia::render('Memorize/Chapter', [
            'book' => $book->only(['id', 'title']),
            'chapter' => $chapter,
            'verses' => $verses,
        ]);
    }
}
