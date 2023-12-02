<?php

// Restricted resources
Route::group([
    'prefix'     => 'account', 
    'middleware' => ['auth']
], function () {

    // Mail settings
    Route::resource('notifications', 'AccountNotificationController', [
        'only' => ['index', 'store']
    ]);

    Route::delete('notifications/override/{entityType}/{entityId}', 'AccountNotificationController@deleteOverride')
        ->name('notifications.delete-override');

    // User profile
    Route::get('security', [ 'uses' => 'AccountSecurityController@security' ])
        ->name('account.security');
    Route::post('merge',  [ 'uses' => 'AccountSecurityController@merge' ])
        ->name('account.merge');
    Route::get('merge/{requestId}/status',  [ 'uses' => 'AccountSecurityController@mergeStatus' ])
        ->name('account.merge-status');
    Route::get('merge/{requestId}/confirm',  [ 'uses' => 'AccountSecurityController@confirmMerge' ])
        ->name('account.confirm-merge');
    Route::post('merge/{requestId}/cancel', [ 'uses' => 'AccountSecurityController@cancelMerge' ])
        ->name('account.cancel-merge');
    Route::post('password',  [ 'uses' => 'AccountSecurityController@createPassword' ])
        ->name('account.password');
});
