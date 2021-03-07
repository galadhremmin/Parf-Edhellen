<?php

// Public unrestricted API
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH
], function () {

    Route::get('book/group',               [ 'uses' => 'BookApiController@getGroups' ])
        ->name('api.book.groups');
    Route::get('book/languages',           [ 'uses' => 'BookApiController@getLanguages' ])
        ->name('api.book.languages');
    Route::get('book/translate/{glossId}', [ 'uses' => 'BookApiController@get' ])
        ->where([ 'glossId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.book.gloss');
    Route::post('book/entities/{groupId}', [ 'uses' => 'BookApiController@entities' ])
        ->where([ 'groupId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.book.entities');
    Route::post('book/translate',          [ 'uses' => 'BookApiController@translate' ])
        ->name('api.book.translate');
    Route::post('book/find',               [ 'uses' => 'BookApiController@find' ])
        ->name('api.book.find');

    Route::get('speech/{id?}',             [ 'uses' => 'SpeechApiController@index' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('inflection/{id?}',         [ 'uses' => 'InflectionApiController@index' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('gloss/{id?}',              [ 'uses' => 'GlossApiController@get' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('gloss/suggest',           [ 'uses' => 'GlossApiController@suggest' ]);

    Route::resource('sentence', 'SentenceApiController', ['only' => [
        'show'
    ]]);

    Route::get('account/{id}/avatar', [ 'uses' => 'AccountApiController@getAvatar' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('account/find',       [ 'uses' => 'AccountApiController@findAccount' ]);
});

// Public, throttled API
Route::group([ 
    'namespace'  => API_NAMESPACE, 
    'prefix'     => API_PATH,
    'middleware' => 'throttle'
], function () {

    Route::post('utility/markdown',              [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
    Route::post('utility/error',                 [ 'uses' => 'UtilityApiController@logError' ]);
});
