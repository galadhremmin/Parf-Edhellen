<?php

// User accounts
Route::group(['middleware' => 'auth'], function () {
    // User profile
    Route::get('/author/edit/{id?}', ['uses' => 'AuthorController@edit'])
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('author.edit-profile');
});
