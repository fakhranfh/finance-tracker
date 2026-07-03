<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'wallet_id' => Wallet::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->numberBetween(1_000, 1_000_000),
            'type' => fake()->randomElement(['income', 'expense']),
            'transaction_date' => fake()->dateTime(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
