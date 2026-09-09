<?php

use App\Http\Controllers\CrosswordController;
use Illuminate\Support\Facades\Route;

require_once __DIR__.'/../constants.php';

// Crossword: index (language pick), play (specific path first), calendar (year? in path)
Route::get('/crossword', [CrosswordController::class, 'index'])
    ->name('crossword.index');

Route::get('/crossword/{languageId}/play/{date}', [CrosswordController::class, 'show'])
    ->where(['languageId' => REGULAR_EXPRESSION_NUMERIC, 'date' => '[0-9]{4}-[0-9]{2}-[0-9]{2}'])
    ->name('crossword.play');

// The calendar used to be a month at a time, at /crossword/{language}/{year}/{month}.
// Puzzles appear weekly, so a month grid was mostly empty squares; it is a year
// of weeks now. The old month addresses are kept and send visitors to the year
// they asked for -- people link to these and the puzzles themselves have not moved.
Route::get('/crossword/{languageId}/{year}/{month}', function (int $languageId, int $year) {
    return redirect()->route('crossword.calendar', [
        'languageId' => $languageId,
        'year' => $year,
    ], 301);
})
    ->where([
        'languageId' => REGULAR_EXPRESSION_NUMERIC,
        'year' => REGULAR_EXPRESSION_NUMERIC,
        'month' => REGULAR_EXPRESSION_NUMERIC,
    ])
    ->name('crossword.calendar.month');

Route::get('/crossword/{languageId}/{year?}', [CrosswordController::class, 'calendar'])
    ->where([
        'languageId' => REGULAR_EXPRESSION_NUMERIC,
        'year' => REGULAR_EXPRESSION_NUMERIC,
    ])
    ->name('crossword.calendar')
    ->defaults('year', null);
