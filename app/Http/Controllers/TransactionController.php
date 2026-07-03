<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\CategoryService;
use App\Services\TransactionService;
use App\Services\TransferService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private TransferService $transferService,
        private WalletService $walletService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Transaction::class);
        $this->authorize('viewAny', Transfer::class);

        $wallets = $this->walletService->get(['user_id' => auth()->id()]);
        $categories = $this->categoryService->get(['user_id' => auth()->id()]);
        $expenseCategories = $categories->where('type', 'expense')->values();
        $incomeCategories = $categories->where('type', 'income')->values();

        if ($wallets->isEmpty() || $expenseCategories->isEmpty() || $incomeCategories->isEmpty()) {
            return redirect()->route('wallets.index')
                ->withErrors(['setup' => 'Please set up at least one wallet and both income and expense categories before recording transactions.']);
        }

        $filters = [
            'user_id' => auth()->id(),
            'wallet_id' => $request->input('wallet_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $transactions = $this->transactionService->get($filters, ['wallet', 'category']);
        $transfers = $this->transferService->get($filters, ['fromWallet', 'toWallet']);

        $history = $transactions->map(fn (Transaction $transaction) => [
            'model' => $transaction,
            'kind' => $transaction->type,
            'date' => $transaction->transaction_date,
        ])->concat($transfers->map(fn (Transfer $transfer) => [
            'model' => $transfer,
            'kind' => 'transfer',
            'date' => $transfer->transfer_date,
        ]))->sortByDesc(fn (array $entry) => $entry['date'])->values();

        return view('transactions.index', compact(
            'history',
            'wallets',
            'expenseCategories',
            'incomeCategories',
            'filters',
        ));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        try {
            if ($data['type'] === 'transfer') {
                $this->transferService->transfer([
                    'user_id' => $data['user_id'],
                    'from_wallet_id' => $data['wallet_id'],
                    'to_wallet_id' => $data['to_wallet_id'],
                    'amount' => $data['amount'],
                    'transfer_date' => $data['transaction_date'],
                    'notes' => $data['notes'] ?? null,
                ]);
            } else {
                unset($data['to_wallet_id']);

                $this->transactionService->record($data);
            }
        } catch (InsufficientBalanceException $e) {
            return redirect()->route('transactions.index')->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }
}
