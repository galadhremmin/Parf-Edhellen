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
    Route::get('privacy', [ 'uses' => 'AccountPrivacyController@privacy' ])
        ->name('account.privacy');
    Route::post('merge',  [ 'uses' => 'AccountPrivacyController@merge' ])
        ->name('account.merge');
    Route::post('password',  [ 'uses' => 'AccountPrivacyController@createPassword' ])
        ->name('account.password');
});
