<?php

namespace App\Security;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * A six-digit code e-mailed to someone and typed back into the same browser, proving they control
 * the inbox. The code lives, hashed, in the session that asked for it, so an unexpected code is of no
 * use to anyone else. Each purpose (signing in, verifying an address) keeps its own.
 */
class InboxCode
{
    public const LIFETIME_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly string $_purpose,
    ) {}

    /**
     * Starts a new challenge for `$accountId`, replacing any earlier one, and returns the code to send.
     *
     * @param  array<string, mixed>  $context  anything the caller needs back once the code is entered
     */
    public function issue(Request $request, int $accountId, array $context = []): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put($this->sessionKey(), [
            'account_id' => $accountId,
            'code_hash' => Hash::make($code),
            'expires_at' => Carbon::now()->addMinutes(self::LIFETIME_MINUTES)->getTimestamp(),
            'attempts' => 0,
            'context' => $context,
        ]);

        return $code;
    }

    /**
     * The open challenge in this session, or null when there is none or it has expired.
     *
     * @return array{account_id: int, context: array<string, mixed>}|null
     */
    public function pending(Request $request): ?array
    {
        $challenge = $request->session()->get($this->sessionKey());
        if (! is_array($challenge) || $challenge['expires_at'] < Carbon::now()->getTimestamp()) {
            return null;
        }

        return [
            'account_id' => $challenge['account_id'],
            'context' => $challenge['context'] ?? [],
        ];
    }

    /**
     * Checks the code. A right code closes the challenge; so do too many wrong ones, after which a
     * new code must be asked for.
     */
    public function check(Request $request, string $code): ChallengeOutcome
    {
        $challenge = $request->session()->get($this->sessionKey());
        if (! is_array($challenge) || $challenge['expires_at'] < Carbon::now()->getTimestamp()) {
            $request->session()->forget($this->sessionKey());

            return ChallengeOutcome::Expired;
        }

        if (! Hash::check(preg_replace('/\s+/', '', $code), $challenge['code_hash'])) {
            $challenge['attempts'] += 1;
            if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                $request->session()->forget($this->sessionKey());

                return ChallengeOutcome::Exhausted;
            }

            $request->session()->put($this->sessionKey(), $challenge);

            return ChallengeOutcome::Wrong;
        }

        $request->session()->forget($this->sessionKey());

        return ChallengeOutcome::Passed;
    }

    public function forget(Request $request): void
    {
        $request->session()->forget($this->sessionKey());
    }

    private function sessionKey(): string
    {
        return 'auth.inbox-code.'.$this->_purpose;
    }
}
