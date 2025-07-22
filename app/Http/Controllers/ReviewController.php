<?php

namespace App\Http\Controllers;

use App\Models\UserVerseProgress;
use App\Services\SpacedRepetitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Finds the first verse in the user's review queue and redirects to it.
     */
    public function start(Request $request)
    {
        $nextReview = $request->user()->versesToReview()->first();

        if ($nextReview) {
            return Redirect::route('review.show', $nextReview);
        }

        // If there's nothing to review, just go back to the dashboard.
        return Redirect::route('dashboard')->with('info', 'No tienes versículos pendientes para repasar.');
    }

    /**
     * Display the review page for a specific user-verse progress record.
     */
    public function show(Request $request, UserVerseProgress $userVerseProgress)
    {
        abort_if($request->user()->cannot('view', $userVerseProgress), 403);

        $userVerseProgress->load('verse.book');

        // --- UPDATED to mirror the SRS Service logic ---
        $currentInterval = $userVerseProgress->interval;

        $nextIntervals = [
            'again' => now()->addMinutes(10)->diffForHumans(null, true),
            'hard' => now()->addDays(
                ($currentInterval > 0) ? ceil($currentInterval * 1.5) : 1
            )->diffForHumans(null, true, false, 2),
            'good' => now()->addDays(
                ($currentInterval > 0) ? ceil($currentInterval * 2.5) : 3
            )->diffForHumans(null, true, false, 2),
        ];

        return Inertia::render('Review/Show', [
            'progress' => $userVerseProgress,
            'nextIntervals' => $nextIntervals,
        ]);
    }

    /**
     * Update the verse progress based on the user's self-assessment.
     */
    public function update(Request $request, UserVerseProgress $userVerseProgress, SpacedRepetitionService $srs)
    {
        abort_if($request->user()->cannot('update', $userVerseProgress), 403);

        $validated = $request->validate([
            'assessment' => 'required|string|in:again,hard,good',
        ]);

        $srs->handleReview($userVerseProgress, $validated['assessment']);

        // --- UPDATED LOGIC TO FIND THE NEXT VERSE ---

        // Find the next verse in the queue that is NOT the one we just reviewed
        // AND not one we've already seen in this session (unless marked 'again').
        $nextReview = $request->user()->versesToReview()
            ->where('id', '!=', $userVerseProgress->id)
            ->first();

        // After handling the update, let's create the flash message.
        $nextReviewMessage = "¡Bien hecho! " . $this->getNextReviewMessage($userVerseProgress);

        if ($nextReview) {
            // If there's another verse, redirect to it.
            return Redirect::route('review.show', $nextReview)->with('review_success', $nextReviewMessage);
        }

        // If no "new" verses are left, check if any were marked as 'again'
        $lapsedReview = $request->user()->verseProgress()
            ->where('status', 'learning')
            ->where('review_at', '<=', now())
            ->orderBy('review_at')
            ->first();

        if ($lapsedReview) {
            return Redirect::route('review.show', $lapsedReview)->with('review_success', $nextReviewMessage);
        }

        // If the queue is truly empty, go back to the dashboard.
        return Redirect::route('dashboard')->with('success', '¡Repaso completado! Has terminado por hoy.');
    }

    /**
     * Helper to generate a user-friendly message for the next review time.
     */
    private function getNextReviewMessage(UserVerseProgress $progress): string
    {
        if ($progress->interval === 0) {
            return "Lo volverás a ver en unos minutos.";
        }
        return "Volverás a ver este versículo " . $progress->review_at->locale('es')->diffForHumans() . ".";
    }
}
