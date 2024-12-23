<?php

// Admin API

use App\Http\Controllers\Api\v2\AccountApiController;
use App\Http\Controllers\Api\v2\GlossApiController;
use App\Http\Controllers\Api\v2\UtilityApiController;
use App\Security\RoleConstants;

Route::group([
    'namespace' => API_NAMESPACE,
    'prefix' => API_PATH,
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {
    Route::delete('gloss/{id}', [GlossApiController::class, 'destroy'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);

    Route::get('account', [AccountApiController::class, 'index']);
    Route::get('account/{id}', [AccountApiController::class, 'getAccount'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::put('account/{id}/verify-email', [AccountApiController::class, 'updateVerifyEmail'])
        ->name('api.account.verify-email');

    Route::get('utility/errors', [UtilityApiController::class, 'getErrors']);
    Route::get('utility/failed-jobs', [UtilityApiController::class, 'getFailedJobs']);
});
