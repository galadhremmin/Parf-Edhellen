<?php

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');

    // Flashcard results
    Route::get('/dashboard/flashcard/{id}/results', [ 'uses' => 'FlashcardController@list' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('flashcard.list');

    // User profile
    Route::get('/author/edit/{id?}',  [ 'uses' => 'AuthorController@edit' ])->name('author.edit-profile');
    Route::post('/author/edit/{id?}', [ 'uses' => 'AuthorController@update' ])->name('author.update-profile');
});
