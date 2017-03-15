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

Route::get('/about',           [ 'uses' => 'AboutController@index' ])->name('about');
Route::get('/about/donations', [ 'uses' => 'AboutController@donations' ])->name('about.donations');

Route::get('/phrases',  [ 'uses' => 'PhrasesController@index' ])->name('phrases');

Route::get('/w/{word}', [ 'uses' => 'BookController@pageForWord' ]);
Route::get('/wt/{id}',  [ 'uses' => 'BookController@pageForTranslationId' ])->where([ 'id' => '[0-9]+' ]);
