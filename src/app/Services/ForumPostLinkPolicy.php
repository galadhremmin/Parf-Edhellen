<?php

namespace App\Services;

use App\Models\Account;
use App\Security\RoleConstants;
use Carbon\Carbon;

/**
 * Decides whether an account is permitted to include hyperlinks in forum posts. The policy exists to
 * curb spam: brand new and unverified accounts are the most common source of fraudulent link posts,
 * so they are blocked from posting links until they have established a minimal track record.
 */
class ForumPostLinkPolicy
{
    /**
     * Number of days that must have elapsed since an account verified its e-mail address before it
     * is trusted to post links.
     */
    public const TRUSTED_AGE_IN_DAYS = 7;

    /**
     * Patterns that identify a hyperlink in any of its common forms.
     */
    private const LINK_PATTERNS = [
        // Explicit scheme, e.g. http://, https://, ftp://
        '/[a-z][a-z0-9+.\-]*:\/\//i',
        // www. prefixed hostnames without a scheme.
        '/(^|[^a-z0-9])www\./i',
        // Bare domains using a common top-level domain, e.g. "free-stuff.shop".
        '/(^|[^a-z0-9@.\-])[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?(?:\.[a-z0-9\-]+)*\.(?:com|net|org|info|biz|io|co|ru|cn|ua|tk|xyz|top|online|site|shop|store|club|vip|live|link|click|pro|app|dev|me|tv|cc|ws|su|us|uk|de|fr|nl|pl|in|ir|info)\b/i',
    ];

    /**
     * Determines whether the specified content contains a hyperlink in any form.
     */
    public function containsLink(?string $content): bool
    {
        if ($content === null || $content === '') {
            return false;
        }

        foreach (self::LINK_PATTERNS as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether the specified account is permitted to post hyperlinks. Administrators and
     * reviewers are always trusted. Everyone else must have posted before and have verified their
     * e-mail address more than {@see self::TRUSTED_AGE_IN_DAYS} days ago.
     */
    public function mayPostLinks(Account $account): bool
    {
        if ($account->isAdministrator() || $account->memberOf(RoleConstants::Reviewers)) {
            return true;
        }

        if (! $this->hasPostedBefore($account)) {
            return false;
        }

        if ($account->email_verified_at === null) {
            return false;
        }

        $verifiedAt = Carbon::parse($account->email_verified_at);

        return $verifiedAt->lte(Carbon::now()->subDays(self::TRUSTED_AGE_IN_DAYS));
    }

    private function hasPostedBefore(Account $account): bool
    {
        return $account->forum_posts()
            ->where('is_deleted', 0)
            ->exists();
    }
}
