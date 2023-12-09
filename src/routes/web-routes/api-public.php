<?php

// Public unrestricted API

use App\Http\Controllers\Api\v2\{
    AccountApiController,
    BookApiController,
    GlossApiController,
    InflectionApiController,
    SpeechApiController,
    UtilityApiController
};

Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH
], function () {

    Route::get('book/group',               [ BookApiController::class, 'getGroups' ])
        ->name('api.book.groups');
    Route::get('book/languages',           [ BookApiController::class, 'getLanguages' ])
        ->name('api.book.languages');
    Route::get('book/translate/{glossId}', [ BookApiController::class, 'get' ])
        ->where([ 'glossId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.book.gloss');
    Route::post('book/entities/{groupId}/{entityId?}', [ BookApiController::class, 'entities' ])
        ->where([
            'groupId' => REGULAR_EXPRESSION_NUMERIC,
            'entityId' => REGULAR_EXPRESSION_NUMERIC
        ])
        ->name('api.book.entities');
    Route::post('book/find',               [ BookApiController::class, 'find' ])
        ->name('api.book.find');

    Route::get('speech/{id?}',             [ SpeechApiController::class, 'index' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('inflection/{id?}',         [ InflectionApiController::class, 'index' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::get('gloss/{id?}',              [ GlossApiController::class, 'get' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('gloss/suggest',           [ GlossApiController::class, 'suggest' ]);

    Route::resource('sentence', 'SentenceApiController', ['only' => [
        'show'
    ]]);

    Route::get('account/{id}/avatar', [ AccountApiController::class, 'getAvatar' ])
        ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::post('account/find',       [ AccountApiController::class, 'findAccount' ]);
});

// Public, throttled API
Route::group([ 
    'namespace'  => API_NAMESPACE, 
    'prefix'     => API_PATH,
    'middleware' => 'throttle'
], function () {

    Route::post('utility/markdown',              [ UtilityApiController::class, 'parseMarkdown' ]);
    Route::post('utility/error',                 [ UtilityApiController::class, 'logError' ]);
});
