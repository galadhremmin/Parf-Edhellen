<?php

require 'constants.php';

// Public unrestricted API
Route::group([ 
    'namespace' => 'Api\v2', 
    'prefix'    => 'api/v2'
], function () {

Route::get('book/languages',           [ 'uses' => 'BookApiController@getLanguages' ]);
Route::get('book/translate/{glossId}', [ 'uses' => 'BookApiController@get' ])
    ->where([ 'glossId' => REGULAR_EXPRESSION_NUMERIC ]);
Route::post('book/translate',          [ 'uses' => 'BookApiController@translate' ]);
Route::post('book/suggest',            [ 'uses' => 'BookApiController@suggest' ]);
Route::post('book/find',               [ 'uses' => 'BookApiController@find' ]);

Route::get('speech/{id?}',             [ 'uses' => 'SpeechApiController@index' ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

Route::get('inflection/{id?}',         [ 'uses' => 'InflectionApiController@index' ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

Route::resource('forum', 'ForumApiController', ['only' => [
    'index', 'show'
]]);
Route::resource('sentence', 'SentenceApiController', ['only' => [
    'show'
]]);
});

// Public, throttled API
Route::group([ 
    'namespace'  => 'Api\v2', 
    'prefix'     => 'api/v2',
    'middleware' => 'throttle'
], function () {

Route::post('utility/markdown',              [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
Route::post('utility/error',                 [ 'uses' => 'UtilityApiController@logError' ]);
});