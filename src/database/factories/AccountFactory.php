<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => $this->faker->userName,
            'email' => $this->faker->unique()->safeEmail,
            'identity' => (string) Str::uuid(),
            'authorization_provider_id' => null, // Default to 0 for testing
            'profile' => 'Lots of personal data.',
            // A working account: unconfirmed addresses are held at the "confirm your e-mail" interstitial.
            'email_verified_at' => now(),
        ];
    }

    /**
     * An account that hasn't confirmed its e-mail address yet.
     */
    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
