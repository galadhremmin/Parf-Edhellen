<?php

// Restricted API
Route::group([ 
    'namespace'  => API_NAMESPACE, 
    'prefix'     => API_PATH,
    'middleware' => 'auth'
], function () {
    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);
});
