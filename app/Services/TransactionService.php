<?php

namespace App\Services;

use App\Repositories\Transaction\TransactionRepositoryInterface;

class TransactionService
{
    protected $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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
