<?php

$urlSeoReg = '[a-z_0-9]+';

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

Route::get('/', [ 'uses' => 'HomeController@index' ])->name('home');

// Common pages
Route::get('/about',                    [ 'uses' => 'AboutController@index'     ])->name('about');
Route::get('/about/donations',          [ 'uses' => 'AboutController@donations' ])->name('about.donations');
Route::get('/author',                   [ 'uses' => 'AuthorController@index'    ])->name('author.my-profile');
Route::get('/author/{id}-{nickname?}',  [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => '[0-9]+', 'nickname' => $urlSeoReg ])->name('author.profile');

// Phrases
Route::get('/phrases',                                         [ 'uses' => 'SentenceController@index'      ])
    ->name('sentences');
Route::get('/phrases/{langId}-{langName}',                     [ 'uses' => 'SentenceController@byLanguage' ])
    ->where(['langName' => $urlSeoReg])->name('sentences.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [ 'uses' => 'SentenceController@bySentence' ])
    ->name('sentences.sentence');

// Dictionary
Route::get('/w/{word}',               [ 'uses' => 'BookController@pageForWord' ]);
Route::get('/wt/{id}',                [ 'uses' => 'BookController@pageForTranslationId' ])
    ->where([ 'id' => '[0-9]+' ])->name('translation.ref');

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');
    Route::get('/author/edit/{id?}',  [ 'uses' => 'AuthorController@edit' ])->name('author.edit-profile');
    Route::post('/author/edit/{id?}', [ 'uses' => 'AuthorController@update' ])->name('author.update-profile');

    Route::resource('speech', 'SpeechController');
    Route::resource('inflection', 'InflectionController');
});

// Authentication
Route::get('/login', 'SocialAuthController@login')->name('login');
Route::get('/logout', 'SocialAuthController@logout')->name('logout');
Route::get('/federated-auth/redirect/{providerName}', 'SocialAuthController@redirect')
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', 'SocialAuthController@callback');

// API
Route::group([ 'namespace' => 'Api\v1', 'prefix' => 'api/v1' ], function () {
    Route::get('book/translate/{translationId}', [ 'uses' => 'BookApiController@get' ]);
    Route::post('book/translate',                [ 'uses' => 'BookApiController@translate' ]);
    Route::post('book/find',                     [ 'uses' => 'BookApiController@find' ]);
    Route::post('utility/markdown',              [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
});