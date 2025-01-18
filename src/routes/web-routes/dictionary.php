<?php

// LEGACY Dictionary

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/w/{word}/{language?}', [BookController::class, 'pageForWord']);
Route::get('/wg/{glossGroupId}-{glossGroupName?}/{externalId}', [BookController::class, 'pageForExternalSource'])
    ->where(['glossGroupId' => REGULAR_EXPRESSION_NUMERIC]);
Route::get('/wt/{id}', [BookController::class, 'pageForGlossId'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('gloss.ref');
Route::get('/wt/{id}/latest', [BookController::class, 'redirectToLatest'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('gloss.ref.latest');
Route::get('/wt/{id}/versions', [BookController::class, 'versions'])
    ->where(['id' => REGULAR_EXPRESSION_NUMERIC])->name('gloss.ref.version');

// ENTITIES Dictionary
Route::get('/e/{groupName}-{groupId}/{word}/{language?}', [BookController::class, 'pageForEntity'])
    ->where(['groupId' => REGULAR_EXPRESSION_NUMERIC])->name('entities.page');
