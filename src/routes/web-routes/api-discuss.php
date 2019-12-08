<?php

// Public unrestricted API for discuss
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/discuss'
], function () {
    Route::get('group',           [ 'uses' => 'DiscussApiController@getGroups' ]);
    Route::get('group/{groupId}', [ 'uses' => 'DiscussApiController@getGroupAndThreads' ])
        ->where([ 'groupId' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::get('thread', [ 'uses' => 'DiscussApiController@getLatestThreads' ]);
    Route::get('thread/{threadId}', [ 'uses' => 'DiscussApiController@getThread' ])
        ->where([ 'threadId' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::get('thread/{entityType}/{entityId}', [ 'uses' => 'DiscussApiController@getThreadByEntity' ])
        ->where([
            'entityType' => '[a-z]+',
            'threadId' => REGULAR_EXPRESSION_NUMERIC
        ]);
    Route::get('thread/resolve/{entityType}/{entityId}', [ 'uses' => 'DiscussApiController@resolveThread' ])
        ->where([
            'entityType' => '[a-z]+',
            'entityId' => REGULAR_EXPRESSION_NUMERIC
        ])->name('discuss.resolve');
    Route::get('thread/resolve-by-post/{postId}', [ 'uses' => 'DiscussApiController@resolveThreadFromPost' ])
        ->where([
            'postId' => REGULAR_EXPRESSION_NUMERIC
        ])->name('discuss.resolve-by-post');
    Route::get('post/{postId}', [ 'uses' => 'DiscussApiController@getPost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ]);

    Route::post('thread/metadata', [ 'uses' => 'DiscussApiController@getThreadMetadata' ]);
    Route::post('like', [ 'uses' => 'DiscussApiController@storeLike' ]);

    Route::post('post', [ 'uses' => 'DiscussApiController@storePost' ]);
    Route::put('post/{postId}', [ 'uses' => 'DiscussApiController@updatePost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ]);
    Route::delete('post/{postId}', [ 'uses' => 'DiscussApiController@deletePost' ])
        ->where([ 'postId' => REGULAR_EXPRESSION_NUMERIC ]);
});
