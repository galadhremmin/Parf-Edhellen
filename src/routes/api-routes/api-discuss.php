<?php

// Public unrestricted API for discuss

use App\Http\Controllers\Api\v3\DiscussApiController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH.'/discuss',
], function () {
    Route::get('group', [DiscussApiController::class, 'getGroups'])
        ->name('api.discuss.groups');
    Route::get('group/{groupId}', [DiscussApiController::class, 'getGroupAndThreads'])
        ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss.group');
    Route::get('thread', [DiscussApiController::class, 'getLatestThreads'])
        ->name('api.discuss.threads');
    Route::get('thread/{threadId}', [DiscussApiController::class, 'getThread'])
        ->where(['threadId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss.thread');
    Route::get('thread/{entityType}/{entityId}', [DiscussApiController::class, 'getThreadByEntity'])
        ->where([
            'entityType' => '[a-z_]+',
            'threadId' => REGULAR_EXPRESSION_NUMERIC,
        ])
        ->name('api.discuss.thread-by-entity');
    Route::get('thread/resolve/{entityType}/{entityId}', [DiscussApiController::class, 'resolveThread'])
        ->where([
            'entityType' => '[a-z_]+',
            'entityId' => REGULAR_EXPRESSION_NUMERIC,
        ])
        ->name('api.discuss.resolve');
    Route::get('thread/resolve-by-post/{postId}', [DiscussApiController::class, 'resolveThreadFromPost'])
        ->where([
            'postId' => REGULAR_EXPRESSION_NUMERIC,
        ])
        ->name('api.discuss.resolve-by-post');
    Route::get('post/{postId}', [DiscussApiController::class, 'getPost'])
        ->where(['postId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss.post');

    Route::post('thread/metadata', [DiscussApiController::class, 'getThreadMetadata'])
        ->name('api.discuss.metadata');
});

// User restricted API for discuss

Route::group([
    'prefix' => API_PATH.'/discuss',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Discuss.',verification.notice'],
], function () {
    Route::post('like', [DiscussApiController::class, 'storeLike'])
        ->name('api.discuss.like');
    Route::post('post', [DiscussApiController::class, 'storePost'])
        ->name('api.discuss.store-post');
    Route::put('post/{postId}', [DiscussApiController::class, 'updatePost'])
        ->where(['postId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss.update-post');
    Route::delete('post/{postId}', [DiscussApiController::class, 'deletePost'])
        ->where(['postId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss.delete-post');
});

// Admin restricted API for discuss

Route::group([
    'prefix' => API_PATH.'/discuss',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators],
], function () {
    Route::put('thread/stick', [DiscussApiController::class, 'updateThreadStickiness'])
        ->name('api.discuss.stick');
    Route::put('thread/move', [DiscussApiController::class, 'updateThreadGroup'])
        ->name('api.discuss.move');
});
