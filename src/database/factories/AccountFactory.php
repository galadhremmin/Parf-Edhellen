<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AuthorizationProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
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
        ];
    }
}
