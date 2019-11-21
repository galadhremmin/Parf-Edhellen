<?php

// Admin API
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH,
    'middleware' => ['auth', 'auth.require-role:Administrators']
], function () {
    Route::delete('gloss/{id}', [ 'uses' => 'GlossApiController@destroy' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('account',       [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',  [ 'uses' => 'AccountApiController@getAccount' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('book/group',    [ 'uses' => 'BookApiController@getGroups' ]);

    Route::get('utility/error', [ 'uses' => 'UtilityApiController@getErrors' ]);
});
