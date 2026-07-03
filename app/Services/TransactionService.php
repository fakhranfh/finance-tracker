<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Repositories\Transaction\TransactionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        protected TransferService $transferService,
        protected WalletService $walletService,
        protected CategoryService $categoryService,
    ) {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Build the data needed for the transaction history page: the user's
     * wallets, income/expense categories, and merged transaction/transfer
     * history, filtered by the given criteria.
     *
     * @param  array{wallet_id?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     * @return array{wallets: Collection, expenseCategories: Collection, incomeCategories: Collection, history: Collection|null}
     */
    public function getHistoryPageData(string $userId, array $filters = []): array
    {
        $formOptions = $this->getFormOptions($userId);
        $wallets = $formOptions['wallets'];
        $expenseCategories = $formOptions['expenseCategories'];
        $incomeCategories = $formOptions['incomeCategories'];

        if ($wallets->isEmpty() || $expenseCategories->isEmpty() || $incomeCategories->isEmpty()) {
            return [
                'wallets' => $wallets,
                'expenseCategories' => $expenseCategories,
                'incomeCategories' => $incomeCategories,
                'history' => null,
            ];
        }

        $history = $this->buildHistory($userId, $filters)->sortByDesc(fn (array $entry) => $entry['date'])->values();

        return [
            'wallets' => $wallets,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'history' => $history,
        ];
    }

    /**
     * Build the wallet and income/expense category options needed to
     * populate the transaction form.
     *
     * @return array{wallets: Collection, expenseCategories: Collection, incomeCategories: Collection}
     */
    public function getFormOptions(string $userId): array
    {
        $wallets = $this->walletService->get(['user_id' => $userId]);
        $categories = $this->categoryService->get(['user_id' => $userId]);

        return [
            'wallets' => $wallets,
            'expenseCategories' => $categories->where('type', TransactionType::Expense)->values(),
            'incomeCategories' => $categories->where('type', TransactionType::Income)->values(),
        ];
    }

    /**
     * Build the sorted, display-ready transaction/transfer history rows
     * consumed by the transaction history data table.
     *
     * @param  array{wallet_id?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     * @return array<int, array{date: string, kind: string, description: string, wallet_label: string, amount: int}>
     */
    public function getHistoryRows(string $userId, array $filters, string $sort = 'date', string $dir = 'desc'): array
    {
        $history = $this->buildHistory($userId, $filters);

        $sortKey = match ($sort) {
            'amount' => fn (array $entry) => $entry['amount'],
            'type' => fn (array $entry) => $entry['kind'],
            'description' => fn (array $entry) => mb_strtolower($entry['description']),
            'wallet' => fn (array $entry) => mb_strtolower($entry['wallet_label']),
            default => fn (array $entry) => $entry['date'],
        };

        return $history->sortBy($sortKey, SORT_REGULAR, $dir === 'desc')
            ->values()
            ->map(fn (array $entry) => [
                'date' => $entry['date']->clone()->setTimezone('UTC')->toIso8601String(),
                'kind' => $entry['kind'],
                'description' => $entry['description'],
                'wallet_label' => $entry['wallet_label'],
                'amount' => $entry['amount'],
            ])
            ->all();
    }

    /**
     * Merge the user's transactions and transfers into a single collection
     * of display-ready history entries, scoped by the given filters.
     *
     * @param  array{wallet_id?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     */
    private function buildHistory(string $userId, array $filters): Collection
    {
        $scopedFilters = array_merge($filters, ['user_id' => $userId]);

        $transactions = $this->get($scopedFilters, ['wallet', 'category']);
        $transfers = $this->transferService->get($scopedFilters, ['fromWallet', 'toWallet']);

        return $transactions->map(fn (Transaction $transaction) => [
            'model' => $transaction,
            'kind' => $transaction->type->value,
            'date' => $transaction->transaction_date,
            'description' => $transaction->notes ?: $transaction->category->name,
            'wallet_label' => $transaction->wallet->name,
            'amount' => $transaction->amount,
        ])->concat($transfers->map(fn (Transfer $transfer) => [
            'model' => $transfer,
            'kind' => 'transfer',
            'date' => $transfer->transfer_date,
            'description' => $transfer->notes ?: 'Wallet transfer',
            'wallet_label' => $transfer->fromWallet->name.' → '.$transfer->toWallet->name,
            'amount' => $transfer->amount,
        ]));
    }

    /**
     * Record a validated transaction form submission, dispatching to a
     * transfer or an income/expense record depending on its type.
     *
     * @param  array{type: string, wallet_id: string, to_wallet_id?: string, category_id?: string, amount: int, transaction_date: string, notes?: string|null}  $data
     *
     * @throws InsufficientBalanceException
     */
    public function storeFromRequest(array $data, string $userId): Transaction|Transfer
    {
        $data['user_id'] = $userId;

        if ($data['type'] === 'transfer') {
            return $this->transferService->transfer([
                'user_id' => $data['user_id'],
                'from_wallet_id' => $data['wallet_id'],
                'to_wallet_id' => $data['to_wallet_id'],
                'amount' => $data['amount'],
                'transfer_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
            ]);
        }

        unset($data['to_wallet_id']);

        return $this->record($data);
    }

    /**
     * Record an income or expense transaction and update the wallet balance.
     *
     * @param  array{user_id: string, wallet_id: string, category_id: string, amount: int, type: string, transaction_date: string, notes?: string|null}  $data
     *
     * @throws InsufficientBalanceException
     */
    public function record(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $wallet = Wallet::lockForUpdate()->findOrFail($data['wallet_id']);

            if ($data['type'] === 'expense' && $wallet->balance < $data['amount']) {
                throw new InsufficientBalanceException;
            }

            $wallet->balance = $data['type'] === 'income'
                ? $wallet->balance + $data['amount']
                : $wallet->balance - $data['amount'];

            $wallet->save();

            return $this->transactionRepository->create($data);
        });
    }

    public function get(array $filters = [], array $with = [])
    {
        return $this->transactionRepository->get($filters, $with);
    }

    public function getAll()
    {
        return $this->transactionRepository->getAll();
    }

    public function find($id)
    {
        return $this->transactionRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->transactionRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->transactionRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->transactionRepository->delete($id);
    }
}
