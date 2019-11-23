<?php

Route::group([ 
    'prefix'     => 'admin', 
    'middleware' => ['auth', 'auth.require-role:Administrators']  
], function () {
    Route::get('user/incognito', 'DashboardController@setIncognito')->name('dashboard.incognito');
});
