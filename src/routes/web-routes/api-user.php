<?php

// Restricted API
Route::group([ 
    'namespace'  => API_NAMESPACE, 
    'prefix'     => API_PATH,
    'middleware' => 'auth'
], function () {

    Route::resource('forum', 'ForumApiController', ['only' => [
        'edit', 'store', 'update', 'destroy'
    ]]);

    Route::post('forum/like/{id}',   [ 'uses' => 'ForumApiController@storeLike'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::delete('forum/like/{id}', [ 'uses' => 'ForumApiController@destroyLike' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::get('forum/subscription/{id}',    [ 'uses' => 'ForumApiController@getSubscription'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('forum/subscription/{id}',   [ 'uses' => 'ForumApiController@storeSubscription'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::delete('forum/subscription/{id}', [ 'uses' => 'ForumApiController@destroySubscription' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);
});
