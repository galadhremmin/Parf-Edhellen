<?php

Route::get('/', [ 'uses' => 'HomeController@index' ])->name('home');

// Common pages
Route::get('/about',                    [ 'uses' => 'AboutController@index'     ])->name('about');
Route::get('/about/cookies',            [ 'uses' => 'AboutController@cookies'   ])->name('about.cookies');
Route::get('/about/privacy',            [ 'uses' => 'AboutController@privacy'   ])->name('about.privacy');
Route::get('/author',                   [ 'uses' => 'AuthorController@index'    ])->name('author.my-profile');
Route::get('/author/{id}',              [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('author.profile-without-nickname');
Route::get('/author/{id}-{nickname}',   [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC, 'nickname' => REGULAR_EXPRESSION_SEO_STRING ])->name('author.profile');
Route::get('/author/{id}/glosses', [ 'uses' => 'AuthorController@glosses' ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('author.glosses');
Route::get('/author/{id}/sentences', [ 'uses' => 'AuthorController@sentences' ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('author.sentences');
Route::get('/author/{id}/posts', [ 'uses' => 'AuthorController@posts' ])
    ->where([ 'id' => REGULAR_EXPRESSION_NUMERIC ])->name('author.posts');

// Unfortuantely a necessity, a landing page for traffic from IP violating addresses.
Route::get('/blocked', [ App\Http\Controllers\IpBlockedController::class, 'index' ])
    ->name('blocked');
