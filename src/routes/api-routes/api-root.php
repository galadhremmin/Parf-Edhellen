<?php

// Root-only API

use App\Http\Controllers\Api\v3\UtilityApiController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH,
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Root, 'verified'],
], function () {
    Route::delete('utility/error/{id}', [UtilityApiController::class, 'deleteError'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.utility.error.delete');
    
    Route::delete('utility/errors/category', [UtilityApiController::class, 'deleteErrorsByCategory'])
        ->name('api.utility.errors.delete-by-category');
});
