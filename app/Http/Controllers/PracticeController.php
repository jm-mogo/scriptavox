<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    public function show(Verse $verse): Response
    {
        $verse->load('book');

        return Inertia::render('Practice/Show', [
            'verse' => $verse,
        ]);
    }
}
