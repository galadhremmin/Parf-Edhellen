<?php

// Flashcards
Route::get('/word-finder', ['uses' => 'WordFinderController@index'])
    ->name('word-finder.index');
Route::get('/word-finder/{languageId}', ['uses' => 'WordFinderController@show'])
    ->where(['languageId' => REGULAR_EXPRESSION_NUMERIC])
    ->name('word-finder.show');
