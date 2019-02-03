<?php

$numericReg = '[0-9]+';
$urlSeoReg = '[a-z_\-0-9]+';

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
Route::get('/about/cookies',            [ 'uses' => 'AboutController@cookies'   ])->name('about.cookies');
Route::get('/about/privacy',            [ 'uses' => 'AboutController@privacy'   ])->name('about.privacy');
Route::get('/author',                   [ 'uses' => 'AuthorController@index'    ])->name('author.my-profile');
Route::get('/author/{id}',              [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => $numericReg ])->name('author.profile-without-nickname');
Route::get('/author/{id}-{nickname}',   [ 'uses' => 'AuthorController@index'    ])
    ->where([ 'id' => $numericReg, 'nickname' => $urlSeoReg ])->name('author.profile');
Route::get('/author/{id}/glosses', [ 'uses' => 'AuthorController@glosses' ])
    ->where([ 'id' => $numericReg ])->name('author.glosses');
Route::get('/author/{id}/sentences', [ 'uses' => 'AuthorController@sentences' ])
    ->where([ 'id' => $numericReg ])->name('author.sentences');
Route::get('/author/{id}/posts', [ 'uses' => 'AuthorController@posts' ])
    ->where([ 'id' => $numericReg ])->name('author.posts');

// Phrases
Route::get('/phrases',                     [ 'uses' => 'SentenceController@index'      ])
    ->name('sentence.public');
Route::get('/phrases/{langId}-{langName}', [ 'uses' => 'SentenceController@byLanguage' ])
    ->where(['langName' => $urlSeoReg])
    ->name('sentence.public.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [ 'uses' => 'SentenceController@bySentence' ])
    ->name('sentence.public.sentence');

// Dictionary
Route::get('/w/{word}/{language?}',   [ 'uses' => 'BookController@pageForWord' ]);
Route::get('/wt/{id}',                [ 'uses' => 'BookController@pageForGlossId' ])
    ->where([ 'id' => $numericReg ])->name('gloss.ref');
    Route::get('/wt/{id}/latest',     [ 'uses' => 'BookController@redirectToLatest' ])
        ->where([ 'id' => $numericReg ])->name('gloss.ref.latest');
Route::get('/wt/{id}/versions',       [ 'uses' => 'BookController@versions' ])
    ->where([ 'id' => $numericReg ])->name('gloss.ref.version');

// Mail cancellation
Route::get('/stop-notification/{token}', ['uses' => 'Resources\\MailSettingController@handleCancellationToken'])
    ->name('mail-setting.cancellation');

// Flashcards
Route::get('/flashcard',       [ 'uses' => 'FlashcardController@index' ])
    ->name('flashcard');
Route::get('/flashcard/{id}',  [ 'uses' => 'FlashcardController@cards' ])
    ->where([ 'id' => $numericReg ])->name('flashcard.cards');
Route::post('/flashcard/card', [ 'uses' => 'FlashcardController@card' ]
    )->name('flashcard.card');
Route::post('/flashcard/test', [ 'uses' => 'FlashcardController@test' ])
    ->name('flashcard.test');

// User accounts
Route::group([ 'middleware' => 'auth' ], function () use ($numericReg) {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');

    // Flashcard results
    Route::get('/dashboard/flashcard/{id}/results', [ 'uses' => 'FlashcardController@list' ])
        ->where([ 'id' => $numericReg ])->name('flashcard.list');

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



// Restricted API
Route::group([ 
        'namespace'  => 'Api\v2', 
        'prefix'     => 'api/v2',
        'middleware' => 'auth'
    ], function () use ($numericReg) {
    
    Route::resource('forum', 'ForumApiController', ['only' => [
        'edit', 'store', 'update', 'destroy'
    ]]);

    Route::post('forum/like/{id}',   [ 'uses' => 'ForumApiController@storeLike'   ])
        ->where([ 'id' => $numericReg ]);
    Route::delete('forum/like/{id}', [ 'uses' => 'ForumApiController@destroyLike' ])
        ->where([ 'id' => $numericReg ]);
    Route::get('forum/subscription/{id}',    [ 'uses' => 'ForumApiController@getSubscription'   ])
        ->where([ 'id' => $numericReg ]);
    Route::post('forum/subscription/{id}',   [ 'uses' => 'ForumApiController@storeSubscription'   ])
        ->where([ 'id' => $numericReg ]);
    Route::delete('forum/subscription/{id}', [ 'uses' => 'ForumApiController@destroySubscription' ])
        ->where([ 'id' => $numericReg ]);

    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);
});

// Admin API
Route::group([ 
        'namespace' => 'Api\v2', 
        'prefix'    => 'api/v2',
        'middleware' => ['auth', 'auth.require-role:Administrators']
    ], function () use ($numericReg) {

    Route::get('account',        [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',   [ 'uses' => 'AccountApiController@getAccount' ]);
    Route::post('account/find',  [ 'uses' => 'AccountApiController@findAccount' ]);

    Route::get('book/group',      [ 'uses' => 'BookApiController@getGroups' ]);

    Route::get('forum/sticky/{id}',   [ 'uses' => 'ForumApiController@getSticky'   ])
        ->where([ 'id' => $numericReg ]);
    Route::post('forum/sticky/{id}',   [ 'uses' => 'ForumApiController@storeSticky'   ])
        ->where([ 'id' => $numericReg ]);
    Route::delete('forum/sticky/{id}', [ 'uses' => 'ForumApiController@destroySticky' ])
        ->where([ 'id' => $numericReg ]);
});
