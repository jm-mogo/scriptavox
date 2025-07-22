<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\UserVerseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UserVerseProgressController extends Controller
{
    /**
     * Add a verse to the authenticated user's personal memorization list.
     * 
     * 
     */

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $myVerses = UserVerseProgress::where('user_id', $userId)
            // Eager load the verse and its book in a single query for performance
            ->with('verse.book')
            // Order by the most recently added first
            ->latest()
            ->paginate(10); // Paginate the results

        return Inertia::render('MyVerses/Index', [
            'versesProgress' => $myVerses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
        ]);

        $userId = $request->user()->id;

        // Use firstOrCreate to prevent adding the same verse twice
        UserVerseProgress::firstOrCreate(
            [
                'user_id' => $userId,
                'verse_id' => $validated['verse_id'],
            ],
            [
                // These values are only set if the record is new
                'status' => 'new',
                'review_at' => now(), // Ready for review immediately
                'interval' => 1,
                'ease_factor' => 2.5,
                // lesson_id is left as NULL, indicating a personal verse
            ]
        );

        // Redirect back with a success message
        return Redirect::back()->with('success', '¡Versículo añadido a tu lista!');
    }
}
