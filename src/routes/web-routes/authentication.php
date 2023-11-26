<?php

// Authentication
Route::get('/login', 'SocialAuthController@login')->name('login');
Route::get('/logout', 'SocialAuthController@logout')->name('logout');
Route::get('/signup', 'SocialAuthController@register')->name('register');
Route::get('/federated-auth/redirect/{providerName}', 'SocialAuthController@redirect')
    ->name('auth.redirect');
Route::get('/federated-auth/callback/{providerName}', 'SocialAuthController@callback');
