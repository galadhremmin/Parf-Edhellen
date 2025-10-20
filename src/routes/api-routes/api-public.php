<?php

// Public unrestricted API

use App\Http\Controllers\Api\v3\AccountApiController;
use App\Http\Controllers\Api\v3\AccountFeedApiController;
use App\Http\Controllers\Api\v3\BookApiController;
use App\Http\Controllers\Api\v3\LexicalEntryApiController;
use App\Http\Controllers\Api\v3\InflectionApiController;
use App\Http\Controllers\Api\v3\SentenceApiController;
use App\Http\Controllers\Api\v3\SpeechApiController;
use App\Http\Controllers\Api\v3\UtilityApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH,
], function () {

    Route::get('book/group', [BookApiController::class, 'getGroups'])
        ->name('api.book.groups');
    Route::get('book/languages', [BookApiController::class, 'getLanguages'])
        ->name('api.book.languages');
    Route::get('book/translate/{glossId}', [BookApiController::class, 'get'])
        ->where(['glossId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.book.gloss');
    Route::get('book/translate/version/{id}', [BookApiController::class, 'getFromVersion'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::post('book/entities/{groupId}/{entityId?}', [BookApiController::class, 'entities'])
        ->where([
            'groupId' => REGULAR_EXPRESSION_NUMERIC,
            'entityId' => REGULAR_EXPRESSION_NUMERIC,
        ])
        ->name('api.book.entities');
    Route::post('book/find', [BookApiController::class, 'find'])
        ->name('api.book.find');

    Route::get('speech/{id?}', [SpeechApiController::class, 'index'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);

    Route::get('inflection/{id?}', [InflectionApiController::class, 'index'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::get('inflection/ungwe/{lexicalEntryId}', [InflectionApiController::class, 'getUngweInflections'])
        ->where(['lexicalEntryId' => REGULAR_EXPRESSION_NUMERIC]);

    Route::get('lexical-entry/{id?}', [LexicalEntryApiController::class, 'get'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::post('lexical-entry/suggest', [LexicalEntryApiController::class, 'suggest']);

    Route::resource('sentence', SentenceApiController::class, ['only' => [
        'show',
    ]]);

    Route::get('account/{id}/avatar', [AccountApiController::class, 'getAvatar'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::post('account/find', [AccountApiController::class, 'findAccount']);
    Route::get('account/{id}/feed', [AccountFeedApiController::class, 'getFeed']);
});

// Public, throttled API
Route::group([
    'prefix' => API_PATH,
    'middleware' => 'throttle',
], function () {

    Route::post('utility/markdown', [UtilityApiController::class, 'parseMarkdown']);
    Route::post('utility/error', [UtilityApiController::class, 'logError']);
});
