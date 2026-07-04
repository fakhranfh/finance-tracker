<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function show(): View
    {
        $userId = auth()->id();

        return view('dashboard', [
            'totalBalance' => $this->dashboardService->getTotalBalance($userId),
            'cashFlow' => $this->dashboardService->getMonthToDateCashFlow($userId),
            'topExpenseCategories' => $this->dashboardService->getTopExpenseCategories($userId),
            'recentTransactions' => $this->dashboardService->getRecentTransactions($userId, 10),
        ]);
    }
}
