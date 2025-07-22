<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VerseExplorerController;
use App\Http\Controllers\UserVerseProgressController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- Public Routes ---
// This is the main landing page for guests.
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


// --- Authenticated Routes ---
// All routes inside this group require the user to be logged in and verified.
Route::middleware(['auth'])->group(function () {

    // Dashboard
    // WRONG - This just renders the component without data
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    // Verse Explorer (Finding and selecting verses)
    Route::get('/explore', [VerseExplorerController::class, 'show'])->name('explore.show');
    // Helper API-like route for the explorer's dynamic dropdowns
    Route::get('/api/books/{book}/chapters/{chapter}/verses', [VerseExplorerController::class, 'getVersesForChapter'])->name('api.verses.for-chapter');

    // User's Personal Verse List (CRUD)
    Route::get('/my-verses', [UserVerseProgressController::class, 'index'])->name('my-verses.index');
    Route::post('/my-verses', [UserVerseProgressController::class, 'store'])->name('my-verses.store');
    // (A route for deleting a verse from the list would go here later, e.g., Route::delete(...))

    // Learning a New Verse (Multi-stage process)
    Route::get('/practice/{verse}', [PracticeController::class, 'show'])->name('practice.show');

    // Reviewing a Learned Verse (SRS "Exam")
    // Add this new route for the start of the review flow
    Route::get('/review/start', [ReviewController::class, 'start'])->name('review.start');
    Route::get('/review/{userVerseProgress}', [ReviewController::class, 'show'])->name('review.show');
    Route::patch('/review/{userVerseProgress}', [ReviewController::class, 'update'])->name('review.update');

    Route::get('/stats', [StatsController::class, 'show'])->name('stats.show');
});


// --- Authentication & Profile Routes ---
// These files typically contain routes for login, registration, profile, etc.
require __DIR__ . '/auth.php';
// The settings.php file is not standard in Laravel, but I'm keeping it as it was in your original file.
// If it's for user profile/settings, it might be better to merge it into the auth group.
require __DIR__ . '/settings.php';
