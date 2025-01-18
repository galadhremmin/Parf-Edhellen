<?php

// Authentication

use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Authentication\OAuthAuthenticationController;
use App\Http\Controllers\Authentication\UsernamePasswordAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthenticationController::class, 'login'])->name('login');
Route::get('/logout', [AuthenticationController::class, 'logout'])->name('logout');
Route::get('/signup', [AuthenticationController::class, 'register'])->name('register');

Route::get('/federated-auth/redirect/{providerName}', [OAuthAuthenticationController::class, 'redirect'])
    ->where(['providerName' => REGULAR_EXPRESSION_SEO_STRING])
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', [OAuthAuthenticationController::class, 'callback'])
    ->where(['providerName' => REGULAR_EXPRESSION_SEO_STRING]);

Route::post('/login/password', [UsernamePasswordAuthenticationController::class, 'loginWithPassword'])
    ->name('auth.password');
Route::post('/register/password', [UsernamePasswordAuthenticationController::class, 'registerWithPassword'])
    ->name('auth.register');
Route::get('/login/password/forgot', [UsernamePasswordAuthenticationController::class, 'forgotPassword'])
    ->name('auth.forgot-password');
Route::post('/login/password/reset', [UsernamePasswordAuthenticationController::class, 'requestPasswordReset'])
    ->middleware('throttle:6,1')
    ->name('auth.reset-password');
Route::get('login/reset-password/{token}', [UsernamePasswordAuthenticationController::class, 'initiatePasswordResetFromEmail'])
    ->name('password.reset');
Route::post('login/reset-password/{token}', [UsernamePasswordAuthenticationController::class, 'completePasswordReset'])
    ->name('password.complete-reset');
