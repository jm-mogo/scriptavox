<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Verse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Start screen: pick a book and chapter, resume saved passages, and repeat what is due.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        $chapterCounts = Verse::query()
            ->selectRaw('book_id, MAX(chapter) as chapter_count')
            ->groupBy('book_id')
            ->pluck('chapter_count', 'book_id');

        $books = Book::orderBy('book_number')
            ->get(['id', 'title'])
            ->filter(fn (Book $book) => $chapterCounts->has($book->id))
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'chapter_count' => (int) $chapterCounts[$book->id],
            ])
            ->values();

        $passages = $user->memorizePassages()
            ->with('book:id,title')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($passage) => [
                'id' => $passage->id,
                'book' => $passage->book->title,
                'chapter' => $passage->chapter,
                'verse_from' => $passage->verse_from,
                'verse_to' => $passage->verse_to,
                'hide_level' => $passage->hide_level,
            ]);

        return Inertia::render('Dashboard', [
            'books' => $books,
            'passages' => $passages,
            'review_count' => $user->versesToReview()->count(),
        ]);
    }
}
