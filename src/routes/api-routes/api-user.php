<?php

// Restricted API

use App\Http\Controllers\Api\v3\AccountApiController;
use App\Http\Controllers\Api\v3\BookApiController;
use App\Http\Controllers\Api\v3\PasskeyApiController;
use App\Http\Controllers\Api\v3\SentenceApiController;
use App\Http\Controllers\Api\v3\SubscriptionApiController;
use App\Http\Controllers\Api\v3\WordListApiController;
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

    // Word Lists API
    Route::get('word-lists', [WordListApiController::class, 'index'])
        ->name('api.word-lists.index');
    Route::post('word-lists', [WordListApiController::class, 'store'])
        ->name('api.word-lists.store');
    Route::get('word-lists/{id}', [WordListApiController::class, 'show'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.show');
    Route::put('word-lists/{id}', [WordListApiController::class, 'update'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.update');
    Route::delete('word-lists/{id}', [WordListApiController::class, 'destroy'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.destroy');
    
    // Word list entries management
    Route::post('word-lists/{id}/entries', [WordListApiController::class, 'addEntry'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.add-entry');
    Route::delete('word-lists/{id}/entries/{entryId}', [WordListApiController::class, 'removeEntry'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC, 'entryId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.remove-entry');
    Route::put('word-lists/{id}/entries/reorder', [WordListApiController::class, 'reorderEntries'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.word-lists.reorder-entries');

    // Passkey management (requires authentication)
    Route::get('passkey', [PasskeyApiController::class, 'getPasskeys'])
        ->middleware('throttle:60,1')
        ->name('api.passkey.index');
    Route::post('passkey/register/challenge', [PasskeyApiController::class, 'generateRegistrationChallenge'])
        ->middleware('throttle:6,1')
        ->name('api.passkey.register-challenge');
    Route::post('passkey/register/verify', [PasskeyApiController::class, 'verifyRegistrationResponse'])
        ->middleware('throttle:12,1')
        ->name('api.passkey.register-verify');
    Route::delete('passkey/{id}', [PasskeyApiController::class, 'deletePasskey'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->middleware('throttle:12,1')
        ->name('api.passkey.delete');
});
