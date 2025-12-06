<?php

// Admin resources

use App\Http\Controllers\Resources\AccountController;
use App\Http\Controllers\Resources\ContributionController;
use App\Http\Controllers\Resources\GlossController;
use App\Http\Controllers\Resources\InflectionController;
use App\Http\Controllers\Resources\SentenceController;
use App\Http\Controllers\Resources\SpeechController;
use App\Http\Controllers\Resources\SystemErrorController;
use App\Security\RoleConstants;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'auth.require-role:'.RoleConstants::Administrators, 'verified'],
], function () {

    Route::resource('account', AccountController::class, [
        'except' => ['show', 'create', 'store', 'update', 'destroy'],
    ]);
    Route::resource('inflection', InflectionController::class, [
        'except' => ['show'],
    ]);
    Route::resource('speech', SpeechController::class, [
        'except' => ['show'],
    ]);
    Route::resource('gloss', GlossController::class, [
        'only' => ['index'],
    ]);
    Route::resource('sentence', SentenceController::class, [
        'only' => ['index', 'destroy'],
    ]);

    Route::resource('system-error', SystemErrorController::class, [
        'only' => ['index'],
    ]);
    Route::get('system-error/connectivity/{component}', [SystemErrorController::class, 'testConnectivity'])
        ->where(['component' => '[a-zA-Z]+'])
        ->name('system-error.connectivity');

    Route::get('sentence/confirm-destroy/{id}', [SentenceController::class, 'confirmDestroy'])->name('sentence.confirm-destroy');

    Route::get('lexical-entry/list/{id}', [GlossController::class, 'listForLanguage'])->name('gloss.list');

    Route::get('account/by-role/{id}', [AccountController::class, 'byRole'])->name('account.by-role');
    Route::delete('account/{id}/delete-membership', [AccountController::class, 'deleteMembership'])->name('account.delete-membership');
    Route::post('account/{id}/add-membership', [AccountController::class, 'addMembership'])->name('account.add-membership');
});
