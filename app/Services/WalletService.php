<?php

namespace App\Services;

use App\Repositories\Wallet\WalletRepositoryInterface;

class WalletService
{
    protected $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function get(array $filters = [], array $with = [])
    {
        return $this->walletRepository->get($filters, $with);
    }

    public function getAll()
    {
        return $this->walletRepository->getAll();
    }

    public function find($id)
    {
        return $this->walletRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->walletRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->walletRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->walletRepository->delete($id);
    }
}
