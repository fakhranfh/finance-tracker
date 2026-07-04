<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\Transaction\TransactionRepositoryInterface;
use App\Repositories\Wallet\WalletRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const BALANCE_CACHE_TTL_SECONDS = 300;

    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected TransactionRepositoryInterface $transactionRepository,
    ) {}

    /**
     * Total balance across all of the user's wallets, read purely from the
     * wallets table and served from the app's default cache store (set
     * CACHE_STORE=redis in .env to switch it to Redis) so the dashboard
     * never touches the transactions table for this figure.
     */
    public function getTotalBalance(string $userId): int
    {
        return Cache::remember(
            $this->totalBalanceCacheKey($userId),
            self::BALANCE_CACHE_TTL_SECONDS,
            fn () => $this->walletRepository->sumBalance($userId),
        );
    }

    public function forgetTotalBalanceCache(string $userId): void
    {
        Cache::forget($this->totalBalanceCacheKey($userId));
    }

    /**
     * @return array{income: int, expense: int}
     */
    public function getMonthToDateCashFlow(string $userId): array
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now();

        return [
            'income' => $this->transactionRepository->sumAmountByTypeForPeriod($userId, TransactionType::Income, $from, $to),
            'expense' => $this->transactionRepository->sumAmountByTypeForPeriod($userId, TransactionType::Expense, $from, $to),
        ];
    }

    /**
     * @return Collection<int, object{category_id: string, category_name: string, total: int}>
     */
    public function getTopExpenseCategories(string $userId)
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now();

        return $this->transactionRepository->topExpenseCategoriesForPeriod($userId, $from, $to);
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getRecentTransactions(string $userId, int $limit = 10)
    {
        return $this->transactionRepository->recent($userId, $limit);
    }

    private function totalBalanceCacheKey(string $userId): string
    {
        return "dashboard:total_balance:{$userId}";
    }
}
