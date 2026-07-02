<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\StoreWalletRequest;
use App\Http\Requests\Wallet\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Wallet::class);

        $wallets = $this->walletService->get(['user_id' => auth()->id()]);
        $totalBalance = $wallets->sum('balance');

        return view('wallets.index', compact('wallets', 'totalBalance'));
    }

    public function store(StoreWalletRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['balance'] ??= 0;

        $this->walletService->create($data);

        return redirect()->route('wallets.index')->with('success', 'Wallet created successfully.');
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $this->walletService->update($wallet->id, $request->validated());

        return redirect()->route('wallets.index')->with('success', 'Wallet updated successfully.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);

        $this->walletService->delete($wallet->id);

        return redirect()->route('wallets.index')->with('success', 'Wallet deleted successfully.');
    }
}
