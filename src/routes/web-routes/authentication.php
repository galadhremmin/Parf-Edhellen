<?php

// Authentication

use App\Http\Controllers\Authentication\{
    AuthenticationController,
    OAuthAuthenticationController,
    UsernamePasswordAuthenticationController
};

Route::get('/login', [ AuthenticationController::class, 'login' ])->name('login');
Route::get('/logout', [ AuthenticationController::class, 'logout' ])->name('logout');
Route::get('/signup', [ AuthenticationController::class, 'register' ])->name('register');

Route::get('/federated-auth/redirect/{providerName}', [ OAuthAuthenticationController::class, 'redirect' ])
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', [ OAuthAuthenticationController::class, 'callback' ]);

Route::post('/login/password', [ UsernamePasswordAuthenticationController::class, 'loginWithPassword' ])
    ->name('auth.password');
Route::post('/register/password', [ UsernamePasswordAuthenticationController::class, 'registerWithPassword' ])
    ->name('auth.register');
Route::get('/login/password/forgot', [ UsernamePasswordAuthenticationController::class, 'forgotPassword' ])
    ->name('auth.forgot-password');
Route::post('/login/password/reset', [ UsernamePasswordAuthenticationController::class, 'resetPassword' ])
    ->middleware('throttle:6,1')
    ->name('auth.reset-password');
Route::get('login/reset-password/{token}', [ UsernamePasswordAuthenticationController::class, 'resetPasswordConfirmedFromEmail' ])
    ->name('password.reset');