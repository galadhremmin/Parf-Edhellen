<?php

namespace App\Services;

use App\Models\Account;
use Closure;

/**
 * The welcome on a new member's own profile: a few ways to make the profile theirs, and two ways into
 * the community. Each step ticks itself off as they do it; the welcome goes once all are done or they
 * hide it. The steps and their order are configured in `ed.welcome`, their wording in `welcome.*`.
 */
class WelcomeChecklist
{
    /**
     * The welcome for `$account` viewed by `$viewer`, ready to show, or null when there's nothing to.
     * Steps come keyed by name, in the configured order, so the profile can put each where it belongs.
     *
     * @return array{done: int, total: int, steps: array<string, array{group: string, title: string, text: string, action: string, url: string, done: bool}>}|null
     */
    public function for(Account $account, ?Account $viewer): ?array
    {
        if ($viewer === null || $viewer->id !== $account->id || ! $account->shows_welcome) {
            return null;
        }

        $states = $this->states($account);
        if (! in_array(false, $states, true)) {
            return null;
        }

        $steps = [];
        foreach (config('ed.welcome') as $group => $configured) {
            foreach ($configured as $key => $step) {
                $steps[$key] = [
                    'group' => $group,
                    'title' => __('welcome.steps.'.$key.'.title'),
                    'text' => __('welcome.steps.'.$key.'.text', ['nickname' => $account->nickname]),
                    'action' => __('welcome.steps.'.$key.'.action'),
                    'url' => route($step['route'], $step['parameters'] ?? []),
                    'done' => $states[$key],
                ];
            }
        }

        return [
            'done' => count(array_filter($states)),
            'total' => count($states),
            'steps' => $steps,
        ];
    }

    /**
     * Whether each configured step is done, in the configured order.
     *
     * @return array<string, bool>
     */
    public function states(Account $account): array
    {
        $checks = $this->checks();
        $states = [];

        foreach (config('ed.welcome') as $steps) {
            foreach (array_keys($steps) as $key) {
                if (! isset($checks[$key])) {
                    throw new \UnexpectedValueException(sprintf('The welcome step "%s" has no check in %s.', $key, self::class));
                }

                $states[$key] = $checks[$key]($account);
            }
        }

        return $states;
    }

    /**
     * Whether the nickname is one they picked, rather than the placeholder ("Account 2503") given to
     * someone whose identity provider shared no name.
     */
    public static function hasChosenName(Account $account): bool
    {
        $placeholder = preg_quote((string) config('ed.default_account_name'), '/');

        return preg_match('/^'.$placeholder.'( \d+)?$/u', (string) $account->nickname) !== 1;
    }

    /**
     * How many steps are left for someone who hid their welcome, so it can be offered back; 0 otherwise.
     */
    public function pendingAfterDismissal(Account $account, ?Account $viewer): int
    {
        if ($viewer === null || $viewer->id !== $account->id || $account->shows_welcome || $account->welcome_dismissed_at === null) {
            return 0;
        }

        return count(array_filter($this->states($account), fn (bool $done) => ! $done));
    }

    public function dismiss(Account $account): void
    {
        $account->shows_welcome = false;
        $account->welcome_dismissed_at = now();
        $account->save();
    }

    /**
     * Brings back a welcome that was hidden. Someone who never had one can't be given one this way.
     */
    public function restore(Account $account): void
    {
        if ($account->welcome_dismissed_at === null) {
            return;
        }

        $account->shows_welcome = true;
        $account->welcome_dismissed_at = null;
        $account->save();
    }

    /**
     * How to tell each step is done; a step in `ed.welcome` needs one here.
     *
     * @return array<string, Closure(Account): bool>
     */
    private function checks(): array
    {
        return [
            'name' => fn (Account $account) => self::hasChosenName($account),
            'avatar' => fn (Account $account) => (bool) $account->has_avatar,
            'introduction' => fn (Account $account) => trim((string) $account->profile) !== '',
            'background' => fn (Account $account) => ! empty($account->feature_background_url),
            'contribution' => fn (Account $account) => $account->contributions()->exists(),
            'discuss' => fn (Account $account) => $account->forum_posts()->exists(),
        ];
    }
}
