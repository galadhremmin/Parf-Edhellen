<?php

// Public unrestricted API

use App\Http\Controllers\Api\v3\CrosswordApiController;
use App\Http\Controllers\Api\v3\WordFinderApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH.'/games',
], function () {
    Route::get('word-finder/{gameId}', [WordFinderApiController::class, 'play'])
        ->where(['gameId' => REGULAR_EXPRESSION_NUMERIC]);

    // Crossword — public
    Route::get('crossword/{languageId}/{date}', [CrosswordApiController::class, 'puzzle'])
        ->where(['languageId' => REGULAR_EXPRESSION_NUMERIC, 'date' => '[0-9]{4}-[0-9]{2}-[0-9]{2}']);
    Route::post('crossword/check', [CrosswordApiController::class, 'check']);
    Route::post('crossword/reveal', [CrosswordApiController::class, 'reveal']);

    // Crossword — admin only
    Route::middleware(['auth'])
        ->get('crossword/{puzzleId}/admin-fill', [CrosswordApiController::class, 'adminFill'])
        ->where(['puzzleId' => REGULAR_EXPRESSION_NUMERIC]);
});
