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
    Route::post('password',  [ 'uses' => 'AccountSecurityController@createPassword' ])
        ->name('account.password');
});
