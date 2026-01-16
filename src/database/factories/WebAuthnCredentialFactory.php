<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\WebAuthnCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebAuthnCredential>
 */
class WebAuthnCredentialFactory extends Factory
{
    protected $model = WebAuthnCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'credential_id' => random_bytes(32),
            'public_key' => json_encode(['kty' => 'EC', 'crv' => 'P-256']), // Mock public key
            'counter' => 0,
            'display_name' => $this->faker->words(2, true),
            'transport' => 'usb',
            'is_active' => true,
            'last_used_at' => null,
        ];
    }

    /**
     * Indicate that the credential is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}



