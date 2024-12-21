<?php

// Flashcards
Route::get('/flashcard', ['uses' => 'FlashcardController@index'])
    ->name('flashcard');
Route::get('/flashcard/{id}', ['uses' => 'FlashcardController@cards'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('flashcard.cards');
Route::post('/flashcard/card', ['uses' => 'FlashcardController@card']
)->name('flashcard.card');
Route::post('/flashcard/test', ['uses' => 'FlashcardController@test'])
    ->name('flashcard.test');
