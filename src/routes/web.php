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
Route::get('/author/{id}/glosses', [ 'uses' => 'AuthorController@glosses' ])
    ->where([ 'id' => '[0-9]+' ])->name('author.glosses');
Route::get('/author/{id}/sentences', [ 'uses' => 'AuthorController@sentences' ])
    ->where([ 'id' => '[0-9]+' ])->name('author.sentences');
Route::get('/author/{id}/posts', [ 'uses' => 'AuthorController@posts' ])
    ->where([ 'id' => '[0-9]+' ])->name('author.posts');

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
    ->where([ 'id' => '[0-9]+' ])->name('gloss.ref');
    Route::get('/wt/{id}/latest',     [ 'uses' => 'BookController@redirectToLatest' ])
        ->where([ 'id' => '[0-9]+' ])->name('gloss.ref.latest');
Route::get('/wt/{id}/versions',       [ 'uses' => 'BookController@versions' ])
    ->where([ 'id' => '[0-9]+' ])->name('gloss.ref.version');

// Mail cancellation
Route::get('/stop-notification/{token}', ['uses' => 'Resources\\MailSettingController@handleCancellationToken'])
    ->name('mail-setting.cancellation');

// User accounts
Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('/dashboard',          [ 'uses' => 'DashboardController@index' ])->name('dashboard');

    // Flashcards
    Route::get('/dashboard/flashcard',       [ 'uses' => 'FlashcardController@index' ])->name('flashcard');
    Route::get('/dashboard/flashcard/{id}',  [ 'uses' => 'FlashcardController@cards' ])
        ->where([ 'id' => '[0-9]+' ])->name('flashcard.cards');
    Route::get('/dashboard/flashcard/{id}/results', [ 'uses' => 'FlashcardController@list' ])
        ->where([ 'id' => '[0-9]+' ])->name('flashcard.list');
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

// Public resources
Route::group([ 
    'namespace'  => 'Resources'
], function () {
    Route::resource('discuss', 'DiscussController', [
        'only' => [ 'index', 'show' ]
    ]);
    Route::get('discuss/find-thread/{id}', 'DiscussController@resolveThread')
        ->where([ 'id' => '[0-9]+' ])->name('discuss.find-thread');
});

// Restricted resources
Route::group([ 
    'namespace'  => 'Resources', 
    'prefix'     => 'dashboard', 
    'middleware' => ['auth']
], function () {

    // Mail settings
    Route::resource('mail-setting', 'MailSettingController', [
        'only' => ['index', 'create', 'store']
    ]);

    // Discuss
    Route::resource('discuss', 'DiscussController', [
        'only' => [ 'create', 'store' ]
    ]);

    // Contribute
    Route::resource('contribution', 'ContributionController', [
        'except' => ['create']
    ]);
    Route::get('contribution/create/{morph}', 'ContributionController@create')
        ->where(['morph' => '[a-z]+'])->name('contribution.create');
    Route::get('contribution/{id}/destroy', 'ContributionController@confirmDestroy')
        ->name('contribution.confirm-destroy');
    Route::post('contribution/substep-validate', 'ContributionController@validateSubstep')
        ->name('contribution.substep-validate');
    Route::post('contribution/validate', 'ContributionController@validateRequest')
        ->name('contribution.validate');

    // Note: it is not a mistake to use the sentence controller in this instance. The functionality
    //       implemented in this method is generic.
    Route::post('contribution/sentence/parse-fragment/{name}', 'SentenceController@parseFragments')
        ->name('contribution.parse-fragment');
});

// Admin resources
Route::group([ 
        'namespace'  => 'Resources', 
        'prefix'     => 'admin', 
        'middleware' => ['auth', 'auth.require-role:Administrators'] 
    ], function () {

    Route::resource('account', 'AccountController', [
        'except' => ['show', 'create', 'store', 'update', 'destroy']
    ]);
    Route::resource('inflection', 'InflectionController', [
        'except' => ['show']
    ]);
    Route::resource('sentence', 'SentenceController', [
        'except' => ['show']
    ]);
    Route::resource('speech', 'SpeechController', [
        'except' => ['show']
    ]);
    Route::resource('gloss', 'GlossController', [
        'except' => ['show']
    ]);
    Route::resource('system-error', 'SystemErrorController', ['only' => [
        'index'
    ]]);

    Route::get('sentence/confirm-destroy/{id}', 'SentenceController@confirmDestroy')->name('sentence.confirm-destroy');
    Route::post('sentence/validate', 'SentenceController@validatePayload');
    Route::post('sentence/validate-fragment', 'SentenceController@validateFragments');
    Route::post('sentence/parse-fragment/{name}', 'SentenceController@parseFragments');

    Route::get('gloss/list/{id}', 'GlossController@listForLanguage')->name('gloss.list');

    Route::get('account/by-role/{id}', 'AccountController@byRole')->name('account.by-role');
    Route::delete('account/{id}/delete-membership', 'AccountController@deleteMembership')->name('account.delete-membership');
    Route::post('account/{id}/add-membership', 'AccountController@addMembership')->name('account.add-membership');

    Route::get('contribution/list', 'ContributionController@list')->name('contribution.list');
    Route::get('contribution/{id}/reject', 'ContributionController@confirmReject')->name('contribution.confirm-reject');
    Route::put('contribution/{id}/approve', 'ContributionController@updateApprove')->name('contribution.approve');
    Route::put('contribution/{id}/reject', 'ContributionController@updateReject')->name('contribution.reject');
});

// Public unrestricted API
Route::group([ 
        'namespace' => 'Api\v2', 
        'prefix'    => 'api/v2'
    ], function () {

    Route::get('book/languages',           [ 'uses' => 'BookApiController@getLanguages' ]);
    Route::get('book/translate/{glossId}', [ 'uses' => 'BookApiController@get' ])
        ->where([ 'glossId' => '[0-9]+' ]);
    Route::post('book/translate',          [ 'uses' => 'BookApiController@translate' ]);
    Route::post('book/suggest',            [ 'uses' => 'BookApiController@suggest' ]);
    Route::post('book/find',               [ 'uses' => 'BookApiController@find' ]);

    Route::get('speech/{id?}',             [ 'uses' => 'SpeechApiController@index' ]);

    Route::get('inflection/{id?}',         [ 'uses' => 'InflectionApiController@index' ]);

    Route::resource('forum', 'ForumApiController', ['only' => [
        'index', 'show'
    ]]);
});

// Public, throttled API
Route::group([ 
        'namespace'  => 'Api\v2', 
        'prefix'     => 'api/v2',
        'middleware' => 'throttle'
    ], function () {

    Route::post('utility/markdown',              [ 'uses' => 'UtilityApiController@parseMarkdown' ]);
    Route::post('utility/error',                 [ 'uses' => 'UtilityApiController@logError' ]);
});

// Restricted API
Route::group([ 
        'namespace'  => 'Api\v2', 
        'prefix'     => 'api/v2',
        'middleware' => 'auth'
    ], function () {
    
    Route::resource('forum', 'ForumApiController', ['only' => [
        'edit', 'store', 'update', 'destroy'
    ]]);

    Route::post('forum/like/{id}',   [ 'uses' => 'ForumApiController@storeLike'   ])
        ->where([ 'id' => '[0-9]+' ]);
    Route::delete('forum/like/{id}', [ 'uses' => 'ForumApiController@destroyLike' ])
        ->where([ 'id' => '[0-9]+' ]);

    Route::get('book/word/{id}',  [ 'uses' => 'BookApiController@getWord'   ]);
    Route::post('book/word/find', [ 'uses' => 'BookApiController@findWord'  ]);
});

// Admin API
Route::group([ 
        'namespace' => 'Api\v2', 
        'prefix'    => 'api/v2',
        'middleware' => ['auth', 'auth.require-role:Administrators']
    ], function () {

    Route::get('account',        [ 'uses' => 'AccountApiController@index' ]);
    Route::get('account/{id}',   [ 'uses' => 'AccountApiController@getAccount' ]);
    Route::post('account/find',  [ 'uses' => 'AccountApiController@findAccount' ]);

    Route::get('book/group',      [ 'uses' => 'BookApiController@getGroups' ]);
});
