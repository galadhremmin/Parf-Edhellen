<?php

// Phrases
Route::get('/phrases',                     [ 'uses' => 'SentenceController@index'      ])
    ->name('sentence.public');
Route::get('/phrases/{langId}-{langName}', [ 'uses' => 'SentenceController@byLanguage' ])
    ->where([
        'langId' => REGULAR_EXPRESSION_NUMERIC, 'langName' => REGULAR_EXPRESSION_SEO_STRING
    ])
    ->name('sentence.public.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [ 'uses' => 'SentenceController@bySentence' ])
->where([
    'langId' => REGULAR_EXPRESSION_NUMERIC, 'langName' => REGULAR_EXPRESSION_SEO_STRING,
    'sentId' => REGULAR_EXPRESSION_NUMERIC, 'sentName' => REGULAR_EXPRESSION_SEO_STRING,
])->name('sentence.public.sentence');
