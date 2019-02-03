<?php

// Phrases
Route::get('/phrases',                     [ 'uses' => 'SentenceController@index'      ])
    ->name('sentence.public');
Route::get('/phrases/{langId}-{langName}', [ 'uses' => 'SentenceController@byLanguage' ])
    ->where(['langName' => REGULAR_EXPRESSION_SEO_STRING])
    ->name('sentence.public.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [ 'uses' => 'SentenceController@bySentence' ])
    ->name('sentence.public.sentence');
