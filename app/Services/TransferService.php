<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Repositories\Transfer\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransferService
{
    protected $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    /**
     * Transfer funds between two wallets.
     *
     * @param  array{user_id: string, from_wallet_id: string, to_wallet_id: string, amount: int, transfer_date: string, notes?: string|null}  $data
     *
     * @throws InsufficientBalanceException
     */
    public function transfer(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            $walletIds = [$data['from_wallet_id'], $data['to_wallet_id']];
            sort($walletIds);

            $wallets = Wallet::whereIn('id', $walletIds)->lockForUpdate()->get()->keyBy('id');

            $fromWallet = $wallets->get($data['from_wallet_id']);
            $toWallet = $wallets->get($data['to_wallet_id']);

            if ($fromWallet->balance < $data['amount']) {
                throw new InsufficientBalanceException;
            }

            $fromWallet->decrement('balance', $data['amount']);
            $toWallet->increment('balance', $data['amount']);

            return $this->transferRepository->create($data);
        });
    }

    /**
     * Cancel (soft delete) a transfer, reversing its effect on both
     * wallets: the source wallet gets the amount back, the destination
     * wallet gives it up.
     *
     * @throws InsufficientBalanceException
     */
    public function cancelTransfer(string $transferId): Transfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = Transfer::lockForUpdate()->findOrFail($transferId);

            $walletIds = [$transfer->from_wallet_id, $transfer->to_wallet_id];
            sort($walletIds);

            $wallets = Wallet::whereIn('id', $walletIds)->lockForUpdate()->get()->keyBy('id');
            $fromWallet = $wallets->get($transfer->from_wallet_id);
            $toWallet = $wallets->get($transfer->to_wallet_id);

            if ($toWallet->balance < $transfer->amount) {
                throw new InsufficientBalanceException;
            }

            $fromWallet->increment('balance', $transfer->amount);
            $toWallet->decrement('balance', $transfer->amount);

            $transfer->delete();

            return $transfer;
        });
    }

    public function get(array $filters = [], array $with = [])
    {
        return $this->transferRepository->get($filters, $with);
    }

    public function getAll()
    {
        return $this->transferRepository->getAll();
    }

    public function find($id)
    {
        return $this->transferRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->transferRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->transferRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->transferRepository->delete($id);
    }
}
