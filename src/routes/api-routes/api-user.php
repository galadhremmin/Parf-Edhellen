<?php

// Restricted API

use App\Http\Controllers\Api\v2\AccountApiController;
use App\Http\Controllers\Api\v2\BookApiController;
use App\Http\Controllers\Api\v2\SentenceApiController;
use App\Http\Controllers\Api\v2\SubscriptionApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH,
    'middleware' => 'auth',
], function () {
    Route::get('book/word/{id}', [BookApiController::class, 'getWord'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::post('book/word/find', [BookApiController::class, 'findWord']);

    Route::post('account/edit/{id?}', [AccountApiController::class, 'update'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.update');
    Route::post('account/avatar/edit/{id?}', [AccountApiController::class, 'updateAvatar'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.update-avatar');
    Route::delete('account/edit/{id?}', [AccountApiController::class, 'delete'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.delete');

    Route::get('account/backgrounds', [AccountApiController::class, 'getFeatureBackgrounds']);
    Route::put('account/background/edit/{id}', [AccountApiController::class, 'updateFeatureBackground']);

    Route::get('subscription/{morph}/{id}', [SubscriptionApiController::class, 'getSubscriptionForEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity');
    Route::post('subscription/{morph}/{id}', [SubscriptionApiController::class, 'subscribeToEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity.subscribe');
    Route::delete('subscription/{morph}/{id}', [SubscriptionApiController::class, 'unsubscribeFromEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity.unsubscribe');

    Route::post('sentence/suggest-glosses', [SentenceApiController::class, 'suggestFragments']);
});
