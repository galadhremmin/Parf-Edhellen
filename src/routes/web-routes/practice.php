<?php

// Practice

use App\Http\Controllers\PracticeController;
use Illuminate\Support\Facades\Route;

Route::get('/practice', [PracticeController::class, 'index'])
    ->name('practice');

// This section used to live at /games. People have it bookmarked and other
// sites link to it, so the old address keeps working -- permanently, so search
// engines move their weight across to /practice rather than treating the two
// as rival copies of the same page.
Route::permanentRedirect('/games', '/practice')
    ->name('games');
