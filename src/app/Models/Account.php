<?php

namespace App\Models;

use App\Models\Versioning\LexicalEntryVersion;
use App\Security\RoleConstants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class Account extends Authenticatable implements Interfaces\IHasFriendlyName, MustVerifyEmail
{
    use Notifiable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'identity', 'authorization_provider_id', 'created_at', 'provider_id',
        'profile', 'has_avatar', 'feature_background_url', 'is_deleted', 'password', 'is_passworded',
        'is_master_account', 'master_account_id', 'email_verified_at', 'has_passkeys', 'last_passkey_auth_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'identity', 'authorization_provider_id', 'remember_token', 'email', 'is_deleted',
        'password', 'is_passworded', 'is_master_account', 'master_account_id', 'email_verified_at',
        'has_passkeys', 'last_passkey_auth_at',
    ];

    /**
     * @return BelongsTo<AuthorizationProvider>
     */
    public function authorization_provider(): BelongsTo
    {
        return $this->belongsTo(AuthorizationProvider::class);
    }

    /**
     * @return BelongsTo<Account>
     */
    public function master_account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'master_account_id', 'id');
    }

    /**
     * @return HasMany<Contribution>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * @return HasMany<FlashcardResult>
     */
    public function flashcard_results(): HasMany
    {
        return $this->hasMany(FlashcardResult::class);
    }

    /**
     * @return HasMany<ForumDiscussion>
     */
    public function forum_discussions(): HasMany
    {
        return $this->hasMany(ForumDiscussion::class);
    }

    /**
     * @return HasMany<ForumPostLike>
     */
    public function forum_post_likes(): HasMany
    {
        return $this->hasMany(ForumPostLike::class);
    }

    /**
     * @return HasMany<ForumPost>
     */
    public function forum_posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    /**
     * @return HasMany<ForumThread>
     */
    public function forum_threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    /**
     * @return HasMany<LexicalEntryInflection>
     */
    public function lexical_entry_inflections(): HasMany
    {
        return $this->hasMany(LexicalEntryInflection::class, 'account_id');
    }

    /**
     * @return HasMany<LexicalEntryVersion>
     */
    public function lexical_entry_versions(): HasMany
    {
        return $this->hasMany(LexicalEntryVersion::class, 'account_id');
    }

    /**
     * @return HasMany<LexicalEntry>
     */
    public function lexical_entries(): HasMany
    {
        return $this->hasMany(LexicalEntry::class, 'account_id');
    }

    /**
     * @return HasMany<Sentence>
     */
    public function sentences(): HasMany
    {
        return $this->hasMany(Sentence::class);
    }

    /**
     * @return HasMany<Word>
     */
    public function words(): HasMany
    {
        return $this->hasMany(Word::class);
    }

    /**
     * @return HasMany<AccountSecurityEvent>
     */
    public function account_security_events(): HasMany
    {
        return $this->hasMany(AccountSecurityEvent::class);
    }

    /**
     * @return HasMany<Account>
     */
    public function linked_accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'master_account_id', 'id');
    }

    /**
     * @return HasManyThrough<Role>
     */
    public function roles(): HasManyThrough
    {
        return $this->hasManyThrough(
            Role::class, AccountRoleRel::class,
            'account_id',
            'id',
            'id',
            'role_id'
        );
    }

    /**
     * @return HasMany<WebAuthnCredential>
     */
    public function webauthn_credentials(): HasMany
    {
        return $this->hasMany(WebAuthnCredential::class);
    }

    /**
     * Get only active passkeys
     */
    public function activeWebauthnCredentials(): HasMany
    {
        return $this->webauthn_credentials()->where('is_active', true);
    }

    public function getFriendlyName()
    {
        return $this->nickname;
    }

    public function getAllRoles() {
        $user = $this;
        return Cache::remember('ed.rol.'.$user->id, 5 * 60 /* seconds */, function () use ($user) {
            return Role::forAccount($user)->pluck('name');
        });
    }

    public function memberOf(string $roleName)
    {
        return $this->getAllRoles()->search($roleName) !== false;
    }

    public function addMembershipTo(string $roleName)
    {
        if ($this->memberOf($roleName)) {
            return;
        }

        $role = Role::firstOrCreate(['name' => $roleName]);
        AccountRoleRel::create([
            'account_id' => $this->id,
            'role_id' => $role->id,
        ]);

        Cache::forget('ed.rol.'.$this->id);
    }

    public function removeMembership(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();

        AccountRoleRel::where([
            'account_id' => $this->id,
            'role_id' => $role->id,
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
     * @return bool
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
     * @param  bool  $v  - whether to go incognito or not (=false).
     * @return void
     */
    public function setIncognito(bool $v)
    {
        $request = request();
        if ($request !== null) {
            $cookie = Cookie::make('ed-usermode', $v ? 'incognito' : 'visible', 60 * 24, null, null,
                isset($_SERVER['HTTPS']) /* = secure */, true /* = HTTP only */);
            Cookie::queue($cookie);
        }
    }
}
