<?php

// Public resources

use App\Http\Controllers\Resources\DiscussController;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Resources',
], function () {
    Route::get('discuss', [DiscussController::class, 'index'])
        ->name('discuss.index');
    Route::get('discuss/{id}-{slug?}', 'DiscussController@group')
        ->where(['id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('discuss.group');
    Route::get('discuss/{groupId}-{groupSlug?}/{id}-{slug?}', [DiscussController::class, 'show'])
        ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC, 'id' => REGULAR_EXPRESSION_NUMERIC])
        ->name('discuss.show');
    Route::get('/top-contributors', [DiscussController::class, 'topMembers'])
        ->name('discuss.members');
    Route::get('/all-contributors', [DiscussController::class, 'allMembers'])
        ->name('discuss.member-list');
});
