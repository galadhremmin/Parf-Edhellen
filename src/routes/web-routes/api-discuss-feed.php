<?php

// Public unrestricted API for discuss
Route::group([
    'namespace' => API_NAMESPACE,
    'prefix' => API_PATH.'/discuss/feed',
], function () {
    Route::get('posts', ['uses' => 'DiscussFeedApiController@getPosts'])
        ->name('api.discuss-feed.posts');
    Route::get('posts/{groupId}', ['uses' => 'DiscussFeedApiController@getPostsInGroup'])
        ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss-feed.posts-in-group');
});
