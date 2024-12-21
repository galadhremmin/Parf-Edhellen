<?php

// Restricted resources

use App\Http\Controllers\AccountMergeController;
use App\Http\Controllers\AccountNotificationController;
use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\AccountVerificationController;

Route::middleware('auth')->prefix('account')->group(function () {

    // User profile
    Route::get('security', [AccountSecurityController::class, 'security'])
        ->name('account.security');

    Route::post('password', [AccountPasswordController::class, 'createPassword'])
        ->name('account.password');

    Route::post('resend-verification', [AccountVerificationController::class, 'verifyAccount'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('account.resent-verification');

    Route::get('verify/{id}/{hash}', [AccountVerificationController::class, 'confirmVerificationFromEmail'])
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');

    Route::get('verification-required', [AccountVerificationController::class, 'verificationNotice'])
        ->name('verification.notice');
});

Route::middleware(['auth', 'verified'])->prefix('account')->group(function () {

    // Mail settings
    Route::resource('notifications', AccountNotificationController::class, [
        'only' => ['index', 'store'],
    ]);
    Route::delete('notifications/override/{entityType}/{entityId}', [AccountNotificationController::class, 'deleteOverride'])
        ->name('notifications.delete-override');

    Route::post('merge', [AccountMergeController::class, 'merge'])
        ->name('account.merge');

    Route::get('merge/{requestId}/status', [AccountMergeController::class, 'mergeStatus'])
        ->name('account.merge-status');

    Route::get('merge/{requestId}/confirm', [AccountMergeController::class, 'confirmMerge'])
        ->name('account.confirm-merge');

    Route::post('merge/{requestId}/cancel', [AccountMergeController::class, 'cancelMerge'])
        ->name('account.cancel-merge');
});
