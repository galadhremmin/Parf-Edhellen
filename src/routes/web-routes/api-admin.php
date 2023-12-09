<?php

// Admin API

use App\Security\RoleConstants;

Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH,
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified']
], function () {
    Route::delete('gloss/{id}', [ 'uses' => 'GlossApiController@destroy' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('account',       [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',  [ 'uses' => 'AccountApiController@getAccount' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('utility/errors', [ 'uses' => 'UtilityApiController@getErrors' ]);
    Route::get('utility/failed-jobs', [ 'uses' => 'UtilityApiController@getFailedJobs' ]);
});
