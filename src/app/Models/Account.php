<?php

namespace App\Models;

use App\Models\Versioning\GlossVersion;
use App\Security\RoleConstants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\{
    Cache,
    Cookie
};

class Account extends Authenticatable implements Interfaces\IHasFriendlyName, MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'identity', 'authorization_provider_id', 'created_at', 'provider_id',
        'profile', 'has_avatar', 'feature_background_url', 'is_deleted', 'password', 'is_passworded',
        'is_master_account', 'master_account_id', 'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'identity', 'authorization_provider_id', 'remember_token', 'email', 'is_deleted',
        'password', 'is_passworded', 'is_master_account', 'master_account_id', 'email_verified_at'
    ];

    public function authorization_provider()
    {
        return $this->belongsTo(AuthorizationProvider::class);
    }

    public function master_account()
    {
        return $this->belongsTo(Account::class, 'master_account_id', 'id');
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function flashcard_results()
    {
        return $this->hasMany(FlashcardResult::class);
    }

    public function forum_discussions()
    {
        return $this->hasMany(ForumDiscussion::class);
    }

    public function forum_post_likes()
    {
        return $this->hasMany(ForumPostLike::class);
    }

    public function forum_posts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forum_threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    public function gloss_inflections()
    {
        return $this->hasMany(GlossInflection::class);
    }

    public function gloss_versions()
    {
        return $this->hasMany(GlossVersion::class);
    }

    public function glosses()
    {
        return $this->hasMany(Gloss::class);
    }

    public function sentences()
    {
        return $this->hasMany(Sentence::class);
    }

    public function words()
    {
        return $this->hasMany(Gloss::class);
    }

    public function linked_accounts()
    {
        return $this->hasMany(Account::class, 'master_account_id', 'id');
    }

    public function roles()
    {
        return $this->hasManyThrough(
            Role::class, AccountRoleRel::class,
            'account_id',
            'id',
            'id',
            'role_id'
        );
    }

    public function getFriendlyName() 
    {
        return $this->nickname;
    }

    public function memberOf(string $roleName) 
    {
        $user = $this;
        $roles = Cache::remember('ed.rol.'.$user->id, 5 * 60 /* seconds */, function() use($user) {
            return Role::forAccount($user)->pluck('name');
        });

        return $roles->search($roleName) !== false;
    }

    public function addMembershipTo(string $roleName)
    {
        if ($this->memberOf($roleName)) {
            return;
        }

        $role = Role::firstOrCreate(['name' => $roleName]);
        AccountRoleRel::create([
            'account_id' => $this->id,
            'role_id'    => $role->id
        ]);

        Cache::forget('ed.rol.'.$this->id);
    }

    public function removeMembership(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();

        AccountRoleRel::where([
            'account_id' => $this->id,
            'role_id'    => $role->id
        ])->delete();

        Cache::forget('ed.rol.'.$this->id);
    }

    public function forgetRoles()
    {
        Cache::forget('ed.rol.'.$this->id);
    }

    public function isRoot() 
    {
        return $this->memberOf(RoleConstants::Root);
    }

    public function isAdministrator() 
    {
        return $this->memberOf(RoleConstants::Administrators) || $this->isRoot();
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

    public function getAuthPasswordName()
    {
        return 'password';
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
