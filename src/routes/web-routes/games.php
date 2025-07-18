<?php

// Games

use App\Http\Controllers\GamesController;
use Illuminate\Support\Facades\Route;

Route::get('/games', [GamesController::class, 'index'])
    ->name('games');
