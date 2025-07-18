<?php

// User accounts

use App\Http\Controllers\FlashcardController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    // Flashcard results
    Route::get('/results/flashcard/{id}', [FlashcardController::class, 'list'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('flashcard.list');
});
