<?php

// Admin API
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH,
    'middleware' => ['auth', 'auth.require-role:Administrators']
], function () {

    Route::get('account',        [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',   [ 'uses' => 'AccountApiController@getAccount' ]);
    Route::post('account/find',  [ 'uses' => 'AccountApiController@findAccount' ]);

    Route::get('book/group',      [ 'uses' => 'BookApiController@getGroups' ]);

    Route::get('forum/sticky/{id}',   [ 'uses' => 'ForumApiController@getSticky'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('forum/sticky/{id}',   [ 'uses' => 'ForumApiController@storeSticky'   ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::delete('forum/sticky/{id}', [ 'uses' => 'ForumApiController@destroySticky' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
});
