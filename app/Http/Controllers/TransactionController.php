<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Transaction::class);
        $this->authorize('viewAny', Transfer::class);

        $filters = [
            'wallet_id' => $request->input('wallet_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $pageData = $this->transactionService->getHistoryPageData(auth()->id(), $filters);

        if ($pageData['history'] === null) {
            return redirect()->route('wallets.index')
                ->withErrors(['setup' => 'Please set up at least one wallet and both income and expense categories before recording transactions.']);
        }

        return view('transactions.index', [
            'history' => $pageData['history'],
            'wallets' => $pageData['wallets'],
            'expenseCategories' => $pageData['expenseCategories'],
            'incomeCategories' => $pageData['incomeCategories'],
            'filters' => array_merge($filters, ['user_id' => auth()->id()]),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $formOptions = $this->transactionService->getFormOptions(auth()->id());

        if ($formOptions['wallets']->isEmpty() || $formOptions['expenseCategories']->isEmpty() || $formOptions['incomeCategories']->isEmpty()) {
            return redirect()->route('wallets.index')
                ->withErrors(['setup' => 'Please set up at least one wallet and both income and expense categories before recording transactions.']);
        }

        return view('transactions.create', [
            'wallets' => $formOptions['wallets'],
            'expenseCategories' => $formOptions['expenseCategories'],
            'incomeCategories' => $formOptions['incomeCategories'],
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        try {
            $this->transactionService->storeFromRequest($request->validated(), auth()->id());
        } catch (InsufficientBalanceException $e) {
            return redirect()->route('transactions.create')->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }
}
