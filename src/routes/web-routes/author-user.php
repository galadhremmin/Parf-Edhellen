<?php

// User accounts

use App\Http\Controllers\AuthorController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    // User profile
    Route::get('/author/edit/{id?}', [AuthorController::class, 'edit'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.edit-profile');
});
