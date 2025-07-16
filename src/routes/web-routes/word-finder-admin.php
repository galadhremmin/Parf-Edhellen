<?php

// Admin API

use App\Http\Controllers\WordFinderConfigController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'word-finder',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {
    Route::get('config', [WordFinderConfigController::class, 'index'])
        ->name('word-finder.config.index');
    Route::post('config', [WordFinderConfigController::class, 'store'])
        ->name('word-finder.config.store');
});
