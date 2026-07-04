<?php

namespace App\Services;

use App\Repositories\Wallet\WalletRepositoryInterface;

class WalletService
{
    protected $walletRepository;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        protected DashboardService $dashboardService,
    ) {
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
        $wallet = $this->walletRepository->create($data);
        $this->dashboardService->forgetTotalBalanceCache($wallet->user_id);

        return $wallet;
    }

    public function update($id, array $data)
    {
        $wallet = $this->walletRepository->update($id, $data);
        $this->dashboardService->forgetTotalBalanceCache($wallet->user_id);

        return $wallet;
    }

    public function delete($id)
    {
        $wallet = $this->walletRepository->find($id);
        $result = $this->walletRepository->delete($id);

        if ($wallet) {
            $this->dashboardService->forgetTotalBalanceCache($wallet->user_id);
        }

        return $result;
    }
}
