<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'AccountID';
    protected $table = 'auth_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Nickname', 'Email', 'Identity', 'Identity', 'DateRegistered', 'ProviderID', 'Configured'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'Identity', 'ProviderID', 'Identity', 'Configured'
    ];

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        return $this->AccountID;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberTokenName()
    {
        return 'RememberToken';
    }

    public function getRememberToken()
    {
        return $this->RememberToken;
    }
}
