<?php

namespace App\Events;

enum AccountSecurityActivityResultEnum: string
{
    case SUCCESS = 'SUCCESS';
    case FAILURE = 'FAILURE'; // Failed to authenticate or register
    case BLOCKED = 'BLOCKED'; // Blocked by Recaptcha
}

