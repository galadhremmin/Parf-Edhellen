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
Route::get('/author/{id}',              [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => '[0-9]+' ])->name('author.profile-without-nickname');
Route::get('/author/{id}-{nickname}',   [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => '[0-9]+', 'nickname' => $urlSeoReg ])->name('author.profile');

// Phrases
Route::get('/phrases',                     [ 'uses' => 'SentenceController@index'      ])
    ->name('sentence.public');
Route::get('/phrases/{langId}-{langName}', [ 'uses' => 'SentenceController@byLanguage' ])
    ->where(['langName' => $urlSeoReg])
    ->name('sentence.public.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [ 'uses' => 'SentenceController@bySentence' ])
    ->name('sentence.public.sentence');

// Dictionary
Route::get('/w/{word}',               [ 'uses' => 'BookController@pageForWord' ]);
Route::get('/wt/{id}',                [ 'uses' => 'BookController@pageForTranslationId' ])
    ->where([ 'id' => '[0-9]+' ])->name('translation.ref');
Route::get('/wt/{id}/versions',       [ 'uses' => 'BookController@versions' ])
    ->where([ 'id' => '[0-9]+' ])->name('translation.ref.version');

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');

    // Flashcards
    Route::get('/dashboard/flashcard',       [ 'uses' => 'FlashcardController@index' ])->name('flashcard');
    Route::get('/dashboard/flashcard/{id}',  [ 'uses' => 'FlashcardController@cards' ])
        ->where([ 'id' => '[0-9]+' ])->name('flashcard.cards');
    Route::post('/dashboard/flashcard/card', [ 'uses' => 'FlashcardController@card' ])->name('flashcard.card');
    Route::post('/dashboard/flashcard/test', [ 'uses' => 'FlashcardController@test' ])->name('flashcard.test');

    // User profile
    Route::get('/author/edit/{id?}',  [ 'uses' => 'AuthorController@edit' ])->name('author.edit-profile');
    Route::post('/author/edit/{id?}', [ 'uses' => 'AuthorController@update' ])->name('author.update-profile');
});

Route::group([ 
    'prefix'     => 'admin', 
    'middleware' => ['auth', 'auth.require-role:Administrators']  
], function () {

    Route::get('user/incognito', 'DashboardController@setIncognito')->name('dashboard.incognito');
});

// Authentication
Route::get('/login', 'SocialAuthController@login')->name('login');
Route::get('/logout', 'SocialAuthController@logout')->name('logout');
Route::get('/federated-auth/redirect/{providerName}', 'SocialAuthController@redirect')
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', 'SocialAuthController@callback');

// Sitemap
Route::get('sitemap/{context}', 'SitemapController@index');

// Restricted resources
Route::group([ 
    'namespace'  => 'Resources', 
    'prefix'     => 'dashboard', 
    'middleware' => ['auth']
], function () {

    // Contribute
    Route::resource('translation-review', 'TranslationReviewController');
    Route::get('translation-review/{id}/destroy', 'TranslationReviewController@confirmDestroy')->name('translation-review.confirm-destroy');
});

// Admin resources
Route::group([ 
        'namespace'  => 'Resources', 
        'prefix'     => 'admin', 
        'middleware' => ['auth', 'auth.require-role:Administrators'] 
    ], function () {

    Route::resource('inflection', 'InflectionController', [
        'except' => ['show']
    ]);
    Route::resource('sentence', 'SentenceController', [
        'except' => ['show']
    ]);
    Route::resource('speech', 'SpeechController', [
        'except' => ['show']
    ]);
    Route::resource('translation', 'TranslationController', [
        'except' => ['show']
    ]);
    Route::resource('system-error', 'SystemErrorController', ['only' => [
        'index'
    ]]);

    Route::get('sentence/confirm-destroy/{id}', 'SentenceController@confirmDestroy')->name('sentence.confirm-destroy');
    Route::post('sentence/validate', 'SentenceController@validatePayload');
    Route::post('sentence/validate-fragment', 'SentenceController@validateFragments');
    Route::post('sentence/parse-fragment/{name}', 'SentenceController@parseFragments');

    Route::get('translation/list/{id}', 'TranslationController@listForLanguage')->name('translation.list');

    Route::get('translation-review/list', 'TranslationReviewController@list')->name('translation-review.list');
    Route::get('translation-review/{id}/reject', 'TranslationReviewController@confirmReject')->name('translation-review.confirm-reject');
    Route::put('translation-review/{id}/approve', 'TranslationReviewController@updateApprove')->name('translation-review.approve');
    Route::put('translation-review/{id}/reject', 'TranslationReviewController@updateReject')->name('translation-review.reject');
    
});

// Public unrestricted API
Route::group([ 
        'namespace' => 'Api\v1', 
        'prefix'    => 'api/v1'
    ], function () {

    Route::get('book/translate/{translationId}', [ 'uses' => 'BookApiController@get' ]);
    Route::post('book/translate',                [ 'uses' => 'BookApiController@translate' ]);
    Route::post('book/suggest',                  [ 'uses' => 'BookApiController@suggest' ]);
    Route::post('book/find',                     [ 'uses' => 'BookApiController@find' ]);

    Route::get('speech/{id?}',                   [ 'uses' => 'SpeechApiController@index' ]);

    Route::get('inflection/{id?}',               [ 'uses' => 'InflectionApiController@index' ]);

    Route::resource('forum', 'ForumApiController', ['only' => [
        'index', 'show'
    ]]);
});

// Public, throttled API
Route::group([ 
        'namespace'  => 'Api\v1', 
        'prefix'     => 'api/v1',
        'middleware' => 'throttle'
    ], function () {

    Route::post('utility/markdown',              [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
    Route::post('utility/error',                 [ 'uses' => 'UtilityApiController@logError' ]);
});

// Restricted API
Route::group([ 
        'namespace'  => 'Api\v1', 
        'prefix'     => 'api/v1',
        'middleware' => 'auth'
    ], function () {
    
    Route::resource('forum', 'ForumApiController', ['only' => [
        'edit', 'store', 'update', 'destroy'
    ]]);

    Route::post('forum/like/{id}',   [ 'uses' => 'ForumApiController@storeLike'   ]);
    Route::delete('forum/like/{id}', [ 'uses' => 'ForumApiController@destroyLike' ]);

    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);
});

// Admin API
Route::group([ 
        'namespace' => 'Api\v1', 
        'prefix'    => 'api/v1',
        'middleware' => ['auth', 'auth.require-role:Administrators']
    ], function () {

    Route::get('account',        [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',   [ 'uses' => 'AccountApiController@getAccount' ]);
    Route::post('account/find',  [ 'uses' => 'AccountApiController@findAccount' ]);

    Route::get('book/group',      [ 'uses' => 'BookApiController@getGroups' ]);
});