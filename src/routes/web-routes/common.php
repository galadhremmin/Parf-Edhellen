<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IpBlockedController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Common pages
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/about/cookies', [AboutController::class, 'cookies'])->name('about.cookies');
Route::get('/about/privacy', [AboutController::class, 'privacy'])->name('about.privacy');
Route::get('/author', [AuthorController::class, 'index'])->name('author.my-profile');
Route::get('/author/{id}', [AuthorController::class, 'index'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.profile-without-nickname');
Route::get('/author/{id}-{nickname}', [AuthorController::class, 'index'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC, 'nickname' => REGULAR_EXPRESSION_SEO_STRING])->name('author.profile');
Route::get('/author/{id}/glosses', [AuthorController::class, 'glosses'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.glosses');
Route::get('/author/{id}/sentences', [AuthorController::class, 'sentences'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.sentences');
Route::get('/author/{id}/posts', [AuthorController::class, 'posts'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.posts');

// Unfortuantely a necessity, a landing page for traffic from IP violating addresses.
Route::get('/blocked', [IpBlockedController::class, 'index'])
    ->name('blocked');
