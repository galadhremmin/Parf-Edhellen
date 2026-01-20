<?php

// Admin resources

use App\Http\Controllers\Resources\ContributionController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'reviewer',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Reviewers, 'verified'],
], function () {
    Route::get('contribution/list', [ContributionController::class, 'list'])->name('admin.contribution.list');
    Route::get('contribution/{id}/reject', [ContributionController::class, 'confirmReject'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('contribution.confirm-reject');
    Route::put('contribution/{id}/approve', [ContributionController::class, 'updateApprove'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('contribution.approve');
    Route::put('contribution/{id}/reject', [ContributionController::class, 'updateReject'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('contribution.reject');
});
