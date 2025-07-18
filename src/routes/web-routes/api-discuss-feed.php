<?php

// Public unrestricted API for discuss

use App\Http\Controllers\Api\v2\DiscussFeedApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => API_PATH.'/discuss/feed',
], function () {
    Route::get('posts', [DiscussFeedApiController::class, 'getPosts'])
        ->name('api.discuss-feed.posts');
    Route::get('posts/{groupId}', [DiscussFeedApiController::class, 'getPostsInGroup'])
        ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC])
        ->name('api.discuss-feed.posts-in-group');
});
