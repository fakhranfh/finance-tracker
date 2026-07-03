<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Repositories\Transaction\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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
