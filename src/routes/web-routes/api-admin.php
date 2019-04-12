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
});
