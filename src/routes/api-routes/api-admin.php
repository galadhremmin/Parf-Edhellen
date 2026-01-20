<?php

// Admin API

use App\Http\Controllers\Api\v3\AccountApiController;
use App\Http\Controllers\Api\v3\LexicalEntryApiController;
use App\Http\Controllers\Api\v3\UtilityApiController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH,
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {
    Route::delete('lexical-entry/{id}', [LexicalEntryApiController::class, 'destroy'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);

    Route::get('account', [AccountApiController::class, 'index']);
    Route::get('account/{id}', [AccountApiController::class, 'getAccount'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::put('account/{id}/verify-email', [AccountApiController::class, 'updateVerifyEmail'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.verify-email');

    Route::get('utility/errors', [UtilityApiController::class, 'getErrors']);
    Route::get('utility/failed-jobs', [UtilityApiController::class, 'getFailedJobs']);
});
