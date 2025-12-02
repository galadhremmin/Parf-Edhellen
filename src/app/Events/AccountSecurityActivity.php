<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class AccountSecurityActivity
{
    use SerializesModels;

    public Account $account;
    public string $type;
    public AccountSecurityActivityResultEnum $result;
    public ?string $ipAddress = null;
    public ?string $userAgent = null;
    public ?array $assessmentResult = null;

    /**
     * Creates a new account security activity event.
     *
     * @param Account $account
     * @param string $type
     * @param ?string $ipAddress
     * @param ?string $userAgent
     * @param ?array $assessmentResult
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
     *
     * @param Account $account
     * @param string $type
     * @param ?string $ipAddress
     * @param ?string $userAgent
     * @param ?array $assessmentResult
     */
    public function __construct(Account $account, string $type, AccountSecurityActivityResultEnum $result, ?string $ipAddress = null, ?string $userAgent = null, ?array $assessmentResult = null)
    {
        $this->account = $account;
        $this->type = $type;
        $this->result = $result;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->assessmentResult = $assessmentResult;
    }
}
