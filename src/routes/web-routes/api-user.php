<?php

// Restricted API
Route::group([ 
    'namespace'  => API_NAMESPACE, 
    'prefix'     => API_PATH,
    'middleware' => 'auth'
], function () {
    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);

    Route::post('account/edit/{id?}',        [ 'uses' => 'AccountApiController@update' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('account/avatar/edit/{id?}', [ 'uses' => 'AccountApiController@updateAvatar' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
});
