<?php

// Authentication
Route::get('/login', 'SocialAuthController@login')->name('login');
Route::get('/logout', 'SocialAuthController@logout')->name('logout');
Route::get('/signup', 'SocialAuthController@register')->name('register');
Route::get('/federated-auth/redirect/{providerName}', 'SocialAuthController@redirect')
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', 'SocialAuthController@callback');
Route::post('/login/password', 'SocialAuthController@loginWithPassword')
    ->name('auth.password');
Route::post('/register/password', 'SocialAuthController@registerWithPassword')
    ->name('auth.register');
Route::get('/login/password/forgot', 'SocialAuthController@forgotPassword')
    ->name('auth.forgot-password');
Route::post('/login/password/reset', 'SocialAuthController@resetPassword')
    ->name('auth.reset-password');