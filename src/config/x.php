<?php

return [
    // OAuth 1.0a credentials for posting to X (Twitter) API v2.
    // Generate Access Token & Secret in the X Developer Portal under
    // your app's "Keys and Tokens" tab. The app must have "Read and Write"
    // permission — regenerate the tokens after changing that setting.
    'api_key'             => env('X_API_KEY', ''),
    'api_secret'          => env('X_API_SECRET', ''),
    'access_token'        => env('X_ACCESS_TOKEN', ''),
    'access_token_secret' => env('X_ACCESS_TOKEN_SECRET', ''),
];
