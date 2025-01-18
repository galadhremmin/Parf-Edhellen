<?php

// Restricted resources

use App\Http\Controllers\Resources\ContributionController;
use App\Http\Controllers\Resources\DiscussController;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Resources',
    'prefix' => 'contribute',
    'middleware' => ['auth'],
], function () {
    // Discuss
    Route::resource('discuss', DiscussController::class, [
        'only' => ['create', 'store'],
    ]);
});

// Restricted resources
Route::group([
    'namespace' => 'Resources',
    'prefix' => 'contribute',
    'middleware' => ['auth', 'verified'],
], function () {
    // Contribute
    Route::resource('contribution', ContributionController::class, [
        'except' => ['create'],
    ]);
    Route::get('contribution/create/{morph}', [ContributionController::class, 'create'])
        ->where(['morph' => '[a-z]+'])->name('contribution.create');
    Route::get('contribution/{id}/destroy', [ContributionController::class, 'confirmDestroy'])
        ->name('contribution.confirm-destroy');
    Route::post('contribution/substep-validate', [ContributionController::class, 'validateSubstep'])
        ->name('contribution.substep-validate');
    Route::post('contribution/validate', [ContributionController::class, 'validateRequest'])
        ->name('contribution.validate');
});
