<?php

// Public unrestricted API for discuss

use App\Security\RoleConstants;

Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/discuss'
], function () {
    Route::get('group',           [ 'uses' => 'DiscussApiController@getGroups' ])
        ->name('api.discuss.groups');
    Route::get('group/{groupId}', [ 'uses' => 'DiscussApiController@getGroupAndThreads' ])
        ->where([ 'groupId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.discuss.group');
    Route::get('thread', [ 'uses' => 'DiscussApiController@getLatestThreads' ])
        ->name('api.discuss.threads');
    Route::get('thread/{threadId}', [ 'uses' => 'DiscussApiController@getThread' ])
        ->where([ 'threadId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.discuss.thread');
    Route::get('thread/{entityType}/{entityId}', [ 'uses' => 'DiscussApiController@getThreadByEntity' ])
        ->where([
            'entityType' => '[a-z]+',
            'threadId' => REGULAR_EXPRESSION_NUMERIC
        ])
        ->name('api.discuss.thread-by-entity');
    Route::get('thread/resolve/{entityType}/{entityId}', [ 'uses' => 'DiscussApiController@resolveThread' ])
        ->where([
            'entityType' => '[a-z]+',
            'entityId' => REGULAR_EXPRESSION_NUMERIC
        ])
        ->name('api.discuss.resolve');
    Route::get('thread/resolve-by-post/{postId}', [ 'uses' => 'DiscussApiController@resolveThreadFromPost' ])
        ->where([
            'postId' => REGULAR_EXPRESSION_NUMERIC
        ])
        ->name('api.discuss.resolve-by-post');
    Route::get('post/{postId}', [ 'uses' => 'DiscussApiController@getPost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.discuss.post');

    Route::post('thread/metadata', [ 'uses' => 'DiscussApiController@getThreadMetadata' ])
        ->name('api.discuss.metadata');
});

// User restricted API for discuss

Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/discuss',
    'middleware' => ['auth']
], function () {
    Route::post('like',            [ 'uses' => 'DiscussApiController@storeLike' ])
        ->name('api.discuss.like');
    Route::post('post', [ 'uses' => 'DiscussApiController@storePost' ])
        ->name('api.discuss.store-post');
    Route::put('post/{postId}', [ 'uses' => 'DiscussApiController@updatePost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.discuss.update-post');
    Route::delete('post/{postId}', [ 'uses' => 'DiscussApiController@deletePost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ])
        ->name('api.discuss.delete-post');
});

// Admin restricted API for discuss

Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/discuss',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators]
], function () {
    Route::put('thread/stick', [ 'uses' => 'DiscussApiController@updateThreadStickiness' ])
        ->name('api.discuss.stick');
    Route::put('thread/move', [ 'uses' => 'DiscussApiController@updateThreadGroup' ])
        ->name('api.discuss.move');
});
