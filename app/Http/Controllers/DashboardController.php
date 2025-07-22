<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        // Get the count of verses due for review today.
        $reviewCount = $user->versesToReview()->count();

        // Get some general stats for display.
        $stats = [
            'total_learned' => $user->verseProgress()->count(),
            'mastered_count' => $user->verseProgress()->where('status', 'mastered')->count(),
            'review_count' => $reviewCount,
            // You can add a more complex streak calculation here later.
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
