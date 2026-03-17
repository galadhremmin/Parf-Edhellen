<?php

use App\Http\Controllers\CrosswordController;
use Illuminate\Support\Facades\Route;

require_once __DIR__ . '/../constants.php';

// Crossword: index (language pick), play (specific path first), calendar (year?/month? in path)
Route::get('/crossword', [CrosswordController::class, 'index'])
    ->name('crossword.index');

Route::get('/crossword/{languageId}/play/{date}', [CrosswordController::class, 'show'])
    ->where(['languageId' => REGULAR_EXPRESSION_NUMERIC, 'date' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'])
    ->name('crossword.play');

Route::get('/crossword/{languageId}/{year?}/{month?}', [CrosswordController::class, 'calendar'])
    ->where([
        'languageId' => REGULAR_EXPRESSION_NUMERIC,
        'year' => REGULAR_EXPRESSION_NUMERIC,
        'month' => REGULAR_EXPRESSION_NUMERIC,
    ])
    ->name('crossword.calendar')
    ->defaults('year', null)
    ->defaults('month', null);
