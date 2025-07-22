<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Verse;
use Inertia\Inertia;

class VerseExplorerController extends Controller
{
    /**
     * Display the verse explorer page.
     */
    public function show()
    {
        // Fetch all books, ordered canonically, for the initial dropdown
        $books = Book::orderBy('book_number')->get(['id', 'title']);

        return Inertia::render('Explore/Index', [
            'books' => $books,
        ]);
    }

    /**
     * API endpoint to get all verses for a given chapter.
     */
    public function getVersesForChapter(Book $book, int $chapter)
    {
        $verses = Verse::where('book_id', $book->id)
            ->where('chapter', $chapter)
            ->orderBy('verse_number')
            ->get(['id', 'verse_number', 'text']);

        return response()->json($verses);
    }
}
