<?php

// Admin API
Route::group([ 
    'prefix'    => 'word-finder',
    'middleware' => ['auth', 'auth.require-role:Administrators']
], function () {
    Route::get('config', [ 'uses' => 'WordFinderConfigController@index' ])
        ->name('word-finder.config.index');
    Route::post('config', [ 'uses' => 'WordFinderConfigController@store' ])
        ->name('word-finder.config.store');
});
