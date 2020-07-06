<?php

// Games
Route::get('/games', [ 'uses' => 'GamesController@index' ])
    ->name('games');
