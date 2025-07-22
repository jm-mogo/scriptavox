<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // 1. Basic Counts
        $totalLearned = $user->verseProgress()->count();
        $masteredCount = $user->verseProgress()->where('status', 'mastered')->count();
        $reviewingCount = $user->verseProgress()->where('status', 'reviewing')->count();

        // 2. Verses per Book (More complex query)
        $versesByBook = DB::table('user_verse_progress')
            ->join('verses', 'user_verse_progress.verse_id', '=', 'verses.id')
            ->join('books', 'verses.book_id', '=', 'books.id')
            ->where('user_verse_progress.user_id', $user->id)
            ->select('books.title as book_title', DB::raw('count(verses.id) as verse_count'))
            ->groupBy('books.title')
            ->orderBy('verse_count', 'desc')
            ->get();

        // 3. Streak Calculation (This is a complex operation)
        // For now, we'll just put a placeholder. A real implementation would need
        // a dedicated table for daily activity or a more complex query.
        $currentStreak = 0; // Placeholder

        return Inertia::render('Stats/Index', [
            'stats' => [
                'totalLearned' => $totalLearned,
                'masteredCount' => $masteredCount,
                'reviewingCount' => $reviewingCount,
                'currentStreak' => $currentStreak,
                'versesByBook' => $versesByBook,
            ],
        ]);
    }
}
