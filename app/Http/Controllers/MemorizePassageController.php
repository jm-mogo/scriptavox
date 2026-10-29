<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\MemorizePassage;
use App\Models\Verse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemorizePassageController extends Controller
{
    /**
     * List the signed-in user's saved passages.
     */
    public function index(Request $request): Response
    {
        $passages = $request->user()->memorizePassages()
            ->with('book:id,title')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (MemorizePassage $passage) => [
                'id' => $passage->id,
                'book' => $passage->book->title,
                'chapter' => $passage->chapter,
                'verse_from' => $passage->verse_from,
                'verse_to' => $passage->verse_to,
                'hide_level' => $passage->hide_level,
            ]);

        // Books with their chapter count and verses per chapter, to build a new passage.
        $counts = Verse::query()
            ->selectRaw('book_id, chapter, COUNT(*) as verse_count')
            ->groupBy('book_id', 'chapter')
            ->get()
            ->groupBy('book_id');

        $books = Book::orderBy('book_number')
            ->get(['id', 'title'])
            ->filter(fn (Book $book) => $counts->has($book->id))
            ->map(function (Book $book) use ($counts) {
                $chapters = $counts[$book->id];

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'chapter_count' => (int) $chapters->max('chapter'),
                    'verses' => $chapters->pluck('verse_count', 'chapter')->map(fn ($n) => (int) $n),
                ];
            })
            ->values();

        return Inertia::render('Passages/Index', [
            'passages' => $passages,
            'books' => $books,
        ]);
    }

    /**
     * Save a range of verses for the signed-in user.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'chapter' => ['required', 'integer', 'min:1'],
            'verse_from' => ['required', 'integer', 'min:1'],
            'verse_to' => ['required', 'integer', 'gte:verse_from'],
        ]);

        $passage = $request->user()->memorizePassages()->firstOrCreate([
            'book_id' => $data['book_id'],
            'chapter' => $data['chapter'],
            'verse_from' => $data['verse_from'],
            'verse_to' => $data['verse_to'],
        ], ['hide_level' => 0]);

        return redirect()->route('passages.show', $passage);
    }

    /**
     * Show a saved passage with its verses, ready to resume.
     */
    public function show(Request $request, MemorizePassage $passage): Response
    {
        abort_unless($passage->user_id === $request->user()->id, 404);

        $book = Book::findOrFail($passage->book_id, ['id', 'title']);

        $verses = Verse::where('book_id', $passage->book_id)
            ->where('chapter', $passage->chapter)
            ->whereBetween('verse_number', [$passage->verse_from, $passage->verse_to])
            ->orderBy('verse_number')
            ->get(['id', 'verse_number', 'text']);

        return Inertia::render('Passages/Show', [
            'passage' => [
                'id' => $passage->id,
                'book' => $book->title,
                'chapter' => $passage->chapter,
                'verse_from' => $passage->verse_from,
                'verse_to' => $passage->verse_to,
                'hide_level' => $passage->hide_level,
            ],
            'verses' => $verses,
        ]);
    }

    /**
     * Save progress (the hide level) for a passage.
     */
    public function update(Request $request, MemorizePassage $passage): RedirectResponse
    {
        abort_unless($passage->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'hide_level' => ['required', 'integer', 'in:0,25,50,75'],
        ]);

        $passage->update($data);

        return back();
    }
}
