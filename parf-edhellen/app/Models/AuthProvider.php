<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthProvider extends Model
{
    protected $table = 'auth_providers';
    protected $primaryKey = 'ProviderID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;
}
