<?php

// Phrases

use App\Http\Controllers\SentenceController;
use Illuminate\Support\Facades\Route;

Route::get('/phrases', [SentenceController::class, 'index'])
    ->name('sentence.public');
Route::get('/phrases/{langId}-{langName}', [SentenceController::class, 'byLanguage'])
    ->where([
        'langId' => REGULAR_EXPRESSION_NUMERIC, 'langName' => REGULAR_EXPRESSION_SEO_STRING,
    ])
    ->name('sentence.public.language');
Route::get('/phrases/{langId}-{langName}/{sentId}-{sentName}', [SentenceController::class, 'bySentence'])
    ->where([
        'langId' => REGULAR_EXPRESSION_NUMERIC, 'langName' => REGULAR_EXPRESSION_SEO_STRING,
        'sentId' => REGULAR_EXPRESSION_NUMERIC, 'sentName' => REGULAR_EXPRESSION_SEO_STRING,
    ])->name('sentence.public.sentence');
