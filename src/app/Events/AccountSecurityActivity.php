<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class AccountSecurityActivity
{
    use SerializesModels;

    /**
     * Creates a new account security activity event.
     *
     */
    public static function fromRequest(Request $request, Account $account, string $type, AccountSecurityActivityResultEnum $result, ?array $assessmentResult = null)
    {
        return new self(
            $account,
            $type,
            $result,
            $request->ip(),
            $request->userAgent(),
            $assessmentResult
        );
    }

    /**
     * Creates a new account security activity event.
     */
    public function __construct(readonly Account $account, readonly string $type, readonly AccountSecurityActivityResultEnum $result, readonly ?string $ipAddress = null, readonly ?string $userAgent = null, readonly ?array $assessmentResult = null)
    {}
}
