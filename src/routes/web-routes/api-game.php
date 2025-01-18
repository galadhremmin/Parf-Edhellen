<?php

// Public unrestricted API

use App\Http\Controllers\Api\v2\WordFinderApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => API_NAMESPACE,
    'prefix' => API_PATH.'/games',
], function () {
    Route::get('word-finder/{gameId}', [WordFinderApiController::class, 'play'])
        ->where(['gameId' => REGULAR_EXPRESSION_NUMERIC]);
});
