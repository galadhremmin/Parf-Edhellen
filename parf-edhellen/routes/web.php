<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ 'uses' => 'HomeController@index' ]);

// Common pages
Route::get('/about',                    [ 'uses' => 'AboutController@index'     ])->name('about');
Route::get('/about/donations',          [ 'uses' => 'AboutController@donations' ])->name('about.donations');
Route::get('/phrases',                  [ 'uses' => 'PhrasesController@index'   ])->name('phrases');
Route::get('/author/{id?}/{nickname?}', [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => '[0-9]+' ])->name('author.profile');

// Dictionary
Route::get('/w/{word}',               [ 'uses' => 'BookController@pageForWord' ]);
Route::get('/wt/{id}',                [ 'uses' => 'BookController@pageForTranslationId' ])
    ->where([ 'id' => '[0-9]+' ])->name('translation.ref');

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');
    Route::get('/author/edit/{id?}',  [ 'uses' => 'AuthorController@edit' ])->name('author.edit-profile');
    Route::post('/author/edit/{id?}', [ 'uses' => 'AuthorController@update' ])->name('author.update-profile');
});

// Authentication
Route::get('/login', 'SocialAuthController@login')->name('login');
Route::get('/logout', 'SocialAuthController@logout')->name('logout');
Route::get('/federated-auth/redirect/{providerName}', 'SocialAuthController@redirect')->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', 'SocialAuthController@callback');

// API
Route::group([ 'namespace' => 'Api\v1', 'prefix' => 'api/v1' ], function () {
    Route::get('book/translate/{word}', [ 'uses' => 'BookApiController@translate' ]);
    Route::post('book/find',            [ 'uses' => 'BookApiController@find' ]);
    Route::post('utility/markdown',     [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
});