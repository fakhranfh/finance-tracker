<?php

namespace Database\Factories;

use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_wallet_id' => Wallet::factory(),
            'to_wallet_id' => Wallet::factory(),
            'amount' => fake()->numberBetween(1_000, 1_000_000),
            'transfer_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
