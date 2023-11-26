<?php

// Restricted resources
Route::group([ 
    'namespace'  => 'Resources', 
    'prefix'     => 'contribute', 
    'middleware' => ['auth']
], function () {
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
});
