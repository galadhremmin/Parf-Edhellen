<?php

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    // Flashcard results
    Route::get('/results/flashcard/{id}', [ 'uses' => 'FlashcardController@list' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('flashcard.list');
});
