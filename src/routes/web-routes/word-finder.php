<?php

// Flashcards
Route::get('/word-finder', [ 'uses' => 'WordFinderController@index' ])
    ->name('word-finder.index');
Route::get('/word-finder/{languageId}', [ 'uses' => 'WordFinderController@show' ])
    ->name('word-finder.show');
