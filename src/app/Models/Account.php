<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\{
    Cache,
    Cookie
};

class Account extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'identity', 'authorization_provider_id', 'created_at', 'provider_id', 'is_configured'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'identity', 'authorization_provider_id', 'is_configured'
    ];

    public function memberOf(string $roleName) 
    {
        $user = $this;
        $roles = Cache::remember('ed.rol.'.$user->id, 5 /* minutes */, function() use($user) {
            return Role::forAccount($user)->pluck('name');
        });
        
        return $roles->search($roleName) !== false;
    }

    public function forgetRoles()
    {
        Cache::forget('ed.rol.'.$this->id);
    }

    public function isAdministrator() 
    {
        return $this->memberOf('Administrators');
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }
    
    /**
     * Determines whether the client has specifically requested to operate in incognito mode.
     *
     * @return boolean
     */
    public function isIncognito() 
    {
        if (! $this->isAdministrator()) {
            return false;
        }

        $request = request();
        return $request !== null && $request->cookie('ed-usermode') === 'incognito';
    }

    /**
     * Registers a cookie specifying whether the client requests to operate in an incognito mode.
     *
     * @param bool $v - whether to go incognito or not (=false).
     * @return void
     */
    public function setIncognito(bool $v)
    {
        $request = request();
        if ($request !== null) {
            $cookie = Cookie::make('ed-usermode', $v ? 'incognito' : 'visible', 60*24, null, null, 
                isset($_SERVER['HTTPS']) /* = secure */, true /* = HTTP only */);
            Cookie::queue($cookie);
        }
    }
}
