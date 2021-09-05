<?php

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
    Route::resource('speech', 'SpeechController', [
        'except' => ['show']
    ]);
    Route::resource('gloss', 'GlossController', [
        'only' => ['index']
    ]);
    Route::resource('sentence', 'SentenceController', [
        'only' => ['index', 'destroy']
    ]);

    Route::resource('system-error', 'SystemErrorController', [
        'only' => ['index']
    ]);
    Route::get('system-error/connectivity/{component}', 'SystemErrorController@testConnectivity')
        ->where([ 'component' => '[a-zA-Z]+' ])
        ->name('system-error.connectivity');

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

