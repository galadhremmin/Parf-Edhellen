<?php

// Flashcards

use App\Http\Controllers\WordFinderController;
use Illuminate\Support\Facades\Route;

Route::get('/word-finder', [WordFinderController::class, 'index'])
    ->name('word-finder.index');
Route::get('/word-finder/{languageId}', [WordFinderController::class, 'show'])
    ->where(['languageId' => REGULAR_EXPRESSION_NUMERIC])
    ->name('word-finder.show');
