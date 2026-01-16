<?php

return [
    // Relying Party Configuration
    // 
    // IMPORTANT: RP ID must NOT include port number, scheme, or path
    // - For localhost: use 'localhost' (not 'localhost:8000')
    // - For production: use your domain without protocol or port (e.g., 'example.com')
    // 
    // The origin includes the full URL with protocol and port (e.g., 'http://localhost:8000')
    // The RP ID is just the domain/hostname part (e.g., 'localhost')
    //
    // WebAuthn can be tested on localhost - browsers treat http://localhost as a secure context
    'rp' => [
        'id' => env('WEBAUTHN_RP_ID', 'localhost'), // Default: localhost (no port)
        'name' => env('WEBAUTHN_RP_NAME', 'Parf Edhellen test environment'),
        'origin' => env('WEBAUTHN_ORIGIN', 'http://localhost:8000'), // Full origin with protocol and port
    ],

    // Challenge Configuration
    'challenge' => [
        'length' => env('WEBAUTHN_CHALLENGE_LENGTH', 32), // bytes
        'timeout' => env('WEBAUTHN_CHALLENGE_TIMEOUT', 60000), // milliseconds
        'session_ttl' => env('WEBAUTHN_SESSION_TTL', 600), // seconds (10 minutes)
    ],

    // Attestation Configuration
    'attestation' => [
        'conveyance' => 'none', // 'none', 'indirect', 'direct', 'enterprise'
        'verify' => false, // Certificate verification (future)
    ],

    // Authenticator Configuration
    'authenticator' => [
        'user_verification' => 'preferred', // 'required', 'preferred', 'discouraged'
        'resident_key' => 'preferred', // 'required', 'preferred', 'discouraged'
    ],

    // Rate Limiting
    'rate_limit' => [
        'challenge_per_minute' => 6,
        'verification_failed_per_minute' => 3,
    ],
];
