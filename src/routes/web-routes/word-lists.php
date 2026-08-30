<?php

// Word lists

use App\Http\Controllers\WordListController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    Route::get('/word-lists', [WordListController::class, 'index'])
        ->name('word-list.index');

    // NOTE: the `study` route is added alongside the flashcard deck. It must be registered here,
    // before the catch-all `{name?}` route below, so that the literal segment is not captured as a
    // list name.
});

// Deliberately outside the auth group: a list flagged `is_public` is readable by anyone. The
// controller is responsible for rejecting a private list belonging to somebody else.
Route::get('/word-lists/{id}/{name?}', [WordListController::class, 'show'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC, 'name' => REGULAR_EXPRESSION_SEO_STRING])
    ->name('word-list.show');
