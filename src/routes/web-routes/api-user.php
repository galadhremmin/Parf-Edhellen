<?php

// Restricted API

use App\Http\Controllers\Api\v2\SentenceApiController;

Route::group([
    'namespace' => API_NAMESPACE,
    'prefix' => API_PATH,
    'middleware' => 'auth',
], function () {
    Route::get('book/word/{id}', ['uses' => 'BookApiController@getWord'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC]);
    Route::post('book/word/find', ['uses' => 'BookApiController@findWord']);

    Route::post('account/edit/{id?}', ['uses' => 'AccountApiController@update'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.update');
    Route::post('account/avatar/edit/{id?}', ['uses' => 'AccountApiController@updateAvatar'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.update-avatar');
    Route::delete('account/edit/{id?}', ['uses' => 'AccountApiController@delete'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.account.delete');

    Route::get('account/backgrounds', ['uses' => 'AccountApiController@getFeatureBackgrounds']);
    Route::put('account/background/edit/{id}', ['uses' => 'AccountApiController@updateFeatureBackground']);

    Route::get('subscription/{morph}/{id}', ['uses' => 'SubscriptionApiController@getSubscriptionForEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity');
    Route::post('subscription/{morph}/{id}', ['uses' => 'SubscriptionApiController@subscribeToEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity.subscribe');
    Route::delete('subscription/{morph}/{id}', ['uses' => 'SubscriptionApiController@unsubscribeFromEntity'])
        ->where(['morph' => REGULAR_EXPRESSION_SEO_STRING, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.subscription.specific-entity.unsubscribe');

    Route::post('sentence/suggest-glosses', [SentenceApiController::class, 'suggestFragments']);
});
