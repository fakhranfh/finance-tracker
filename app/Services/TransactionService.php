<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
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
        $wallets = $this->walletService->get(['user_id' => $userId]);
        $categories = $this->categoryService->get(['user_id' => $userId]);
        $expenseCategories = $categories->where('type', TransactionType::Expense)->values();
        $incomeCategories = $categories->where('type', TransactionType::Income)->values();

        if ($wallets->isEmpty() || $expenseCategories->isEmpty() || $incomeCategories->isEmpty()) {
            return [
                'wallets' => $wallets,
                'expenseCategories' => $expenseCategories,
                'incomeCategories' => $incomeCategories,
                'history' => null,
            ];
        }

        $scopedFilters = array_merge($filters, ['user_id' => $userId]);

        $transactions = $this->get($scopedFilters, ['wallet', 'category']);
        $transfers = $this->transferService->get($scopedFilters, ['fromWallet', 'toWallet']);

        $history = $transactions->map(fn (Transaction $transaction) => [
            'model' => $transaction,
            'kind' => $transaction->type,
            'date' => $transaction->transaction_date,
        ])->concat($transfers->map(fn ($transfer) => [
            'model' => $transfer,
            'kind' => 'transfer',
            'date' => $transfer->transfer_date,
        ]))->sortByDesc(fn (array $entry) => $entry['date'])->values();

        return [
            'wallets' => $wallets,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'history' => $history,
        ];
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
