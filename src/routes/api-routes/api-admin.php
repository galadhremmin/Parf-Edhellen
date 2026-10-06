<?php

// Admin API

use App\Http\Controllers\Api\v3\AccountApiController;
use App\Http\Controllers\Api\v3\LexicalEntryApiController;
use App\Http\Controllers\Api\v3\SenseReviewApiController;
use App\Http\Controllers\Api\v3\UtilityApiController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH,
    'middleware' => ['reject.crawlers', 'auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {
    Route::delete('lexical-entry/{id}', [LexicalEntryApiController::class, 'destroy'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);

    Route::get('account', [AccountApiController::class, 'index']);
    Route::get('account/{id}', [AccountApiController::class, 'getAccount'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::put('account/{id}/verify-email', [AccountApiController::class, 'updateVerifyEmail'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.verify-email');

    Route::get('sense-review/next', [SenseReviewApiController::class, 'next'])
        ->name('api.sense-review.next');
    Route::post('sense-review/{id}', [SenseReviewApiController::class, 'decide'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.sense-review.decide');
    Route::post('sense-review/{id}/reword', [SenseReviewApiController::class, 'reword'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.sense-review.reword');

    Route::get('utility/errors', [UtilityApiController::class, 'getErrors']);
    Route::get('utility/account/{id}/ip-history', [UtilityApiController::class, 'getAccountIpHistory'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.utility.account-ip-history');
    Route::get('utility/failed-jobs', [UtilityApiController::class, 'getFailedJobs']);
});
