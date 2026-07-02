<?php

namespace App\Services;

use App\Repositories\Transfer\TransferRepositoryInterface;

class TransferService
{
    protected $transferRepository;

    public function __construct(TransferRepositoryInterface $transferRepository)
    {
        $this->transferRepository = $transferRepository;
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
