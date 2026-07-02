<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FinanceSeeder extends Seeder
{
    /**
     * Seed realistic finance data: many users, each with several wallets,
     * income/expense categories, hundreds of transactions and transfers,
     * with wallet balances reconciled against their ledger history.
     */
    public function run(): void
    {
        User::factory(30)
            ->create()
            ->each(function (User $user): void {
                $wallets = Wallet::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->for($user)
                    ->state(['balance' => 0])
                    ->create();

                $categories = collect([
                    ...Category::factory()->count(4)->for($user)->state(['type' => 'income'])->create(),
                    ...Category::factory()->count(8)->for($user)->state(['type' => 'expense'])->create(),
                ]);

                $incomeCategories = $categories->where('type', 'income');
                $expenseCategories = $categories->where('type', 'expense');

                Transaction::factory()
                    ->count(fake()->numberBetween(200, 500))
                    ->for($user)
                    ->state(function () use ($wallets, $incomeCategories, $expenseCategories) {
                        $type = fake()->randomElement(['income', 'expense']);

                        return [
                            'wallet_id' => $wallets->random()->id,
                            'category_id' => ($type === 'income' ? $incomeCategories : $expenseCategories)->random()->id,
                            'type' => $type,
                        ];
                    })
                    ->create();

                if ($wallets->count() > 1) {
                    Transfer::factory()
                        ->count(fake()->numberBetween(10, 30))
                        ->for($user)
                        ->state(function () use ($wallets) {
                            [$from, $to] = $wallets->random(2)->values();

                            return [
                                'from_wallet_id' => $from->id,
                                'to_wallet_id' => $to->id,
                            ];
                        })
                        ->create();
                }

                $this->reconcileWalletBalances($wallets);
            });
    }

    /**
     * Recompute each wallet's balance from its transaction and transfer history.
     *
     * @param  Collection<int, Wallet>  $wallets
     */
    private function reconcileWalletBalances(Collection $wallets): void
    {
        $wallets->each(function (Wallet $wallet): void {
            $incoming = Transaction::query()
                ->where('wallet_id', $wallet->id)
                ->where('type', 'income')
                ->sum('amount');

            $outgoing = Transaction::query()
                ->where('wallet_id', $wallet->id)
                ->where('type', 'expense')
                ->sum('amount');

            $transfersIn = Transfer::query()->where('to_wallet_id', $wallet->id)->sum('amount');
            $transfersOut = Transfer::query()->where('from_wallet_id', $wallet->id)->sum('amount');

            $wallet->update([
                'balance' => $incoming - $outgoing + $transfersIn - $transfersOut,
            ]);
        });
    }
}
