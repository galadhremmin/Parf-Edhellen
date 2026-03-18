<?php

// Admin

use App\Http\Controllers\CrosswordConfigController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'crossword',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {
    Route::get('config', [CrosswordConfigController::class, 'index'])
        ->name('crossword.config.index');
    Route::post('config', [CrosswordConfigController::class, 'store'])
        ->name('crossword.config.store');
});
