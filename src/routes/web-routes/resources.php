<?php

// Public resources
Route::group([
    'namespace' => 'Resources',
], function () {
    Route::get('discuss', 'DiscussController@index')
        ->name('discuss.index');
    Route::get('discuss/{id}-{slug?}', 'DiscussController@group')
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('discuss.group');
    Route::get('discuss/{groupId}-{groupSlug?}/{id}-{slug?}', 'DiscussController@show')
        ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('discuss.show');
    Route::get('/top-contributors', 'DiscussController@topMembers')
        ->name('discuss.members');
    Route::get('/all-contributors', 'DiscussController@allMembers')
        ->name('discuss.member-list');
});
