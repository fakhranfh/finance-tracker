<?php

use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Wallet;
use Database\Seeders\FinanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every wallet balance reconciles with its transaction and transfer history', function () {
    $this->seed(FinanceSeeder::class);

    expect(Wallet::query()->count())->toBeGreaterThan(0);

    Wallet::all()->each(function (Wallet $wallet): void {
        $incoming = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', 'income')
            ->sum('amount');

        $outgoing = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', 'expense')
            ->sum('amount');

        $transfersIn = Transfer::query()->where('to_wallet_id', $wallet->id)->sum('amount');
        $transfersOut = Transfer::query()->where('from_wallet_id', $wallet->id)->sum('amount');

        $expectedBalance = $incoming - $outgoing + $transfersIn - $transfersOut;

        expect($wallet->balance)->toBe((int) $expectedBalance);
    });
});
