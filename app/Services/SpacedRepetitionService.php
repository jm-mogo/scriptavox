<?php

namespace App\Services;

use App\Models\UserVerseProgress;

class SpacedRepetitionService
{
    public function handleReview(UserVerseProgress $progress, string $assessment): void
    {
        if ($assessment === 'again') {
            // User didn't remember. Reset interval and schedule for re-learning.
            $progress->interval = 0; // Mark as in re-learning step
            $progress->review_at = now()->addMinutes(10);
            $progress->status = 'learning';
        } elseif ($assessment === 'hard') {
            // =====================================================================
            //  THE FIX IS HERE: Ensure the interval grows from 0
            // =====================================================================
            // If the user was re-learning (interval 0), the next step is 1 day.
            // Otherwise, apply the normal multiplier.
            $newInterval = ($progress->interval > 0) ? ceil($progress->interval * 1.5) : 1;
            $progress->interval = $newInterval;
            // =====================================================================

            $progress->review_at = now()->addDays((int) $progress->interval);
            $progress->status = 'reviewing';
        } else { // 'good'
            // =====================================================================
            //  THE FIX IS HERE: Ensure the interval grows from 0
            // =====================================================================
            // If the user was re-learning (interval 0), the next step is 1-3 days.
            // Let's make the "Good" button a bit more rewarding for re-learning.
            $newInterval = ($progress->interval > 0) ? ceil($progress->interval * 2.5) : 3;
            $progress->interval = $newInterval;
            // =====================================================================

            $progress->review_at = now()->addDays((int) $progress->interval);
            $progress->status = 'reviewing';
        }

        $progress->interval = min($progress->interval, 1825);

        // This logic is fine, but we only want to set to 'mastered' if coming from a 'reviewing' state
        if ($progress->interval > 100 && $progress->status === 'reviewing') {
            $progress->status = 'mastered';
        }

        $progress->save();
    }
}
