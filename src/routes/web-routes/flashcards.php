<?php

// Flashcards

use App\Http\Controllers\FlashcardController;
use Illuminate\Support\Facades\Route;

Route::get('/flashcard', [FlashcardController::class, 'index'])
    ->name('flashcard');
Route::get('/flashcard/{id}', [FlashcardController::class, 'cards'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('flashcard.cards');
Route::post('/flashcard/card', [FlashcardController::class, 'card']
)->name('flashcard.card');
Route::post('/flashcard/test', [FlashcardController::class, 'test'])
    ->name('flashcard.test');
