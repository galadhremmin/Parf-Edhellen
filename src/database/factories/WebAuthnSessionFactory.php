<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\WebAuthnSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebAuthnSession>
 */
class WebAuthnSessionFactory extends Factory
{
    protected $model = WebAuthnSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $challenge = base64_encode(random_bytes(32));

        return [
            'challenge' => $challenge,
            'account_id' => null,
            'email' => null,
            'session_type' => 'registration',
            'challenge_data' => [
                'challenge' => $challenge,
                'session_id' => (string) Str::uuid(),
            ],
            'expires_at' => Carbon::now()->addHour(),
        ];
    }

    /**
     * Indicate that the session is for registration
     */
    public function registration(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = base64_encode(random_bytes(32));
            return [
                'session_type' => 'registration',
                'account_id' => Account::factory(),
                'email' => null,
                'challenge_data' => array_merge($attributes['challenge_data'] ?? [], [
                    'challenge' => $challenge,
                    'session_id' => (string) Str::uuid(),
                ]),
            ];
        });
    }

    /**
     * Indicate that the session is for authentication
     */
    public function authentication(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = base64_encode(random_bytes(32));
            return [
                'session_type' => 'authentication',
                'account_id' => null,
                'email' => $this->faker->email,
                'challenge_data' => array_merge($attributes['challenge_data'] ?? [], [
                    'challenge' => $challenge,
                    'session_id' => (string) Str::uuid(),
                ]),
            ];
        });
    }

    /**
     * Indicate that the session is expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->subHour(),
        ]);
    }
}


