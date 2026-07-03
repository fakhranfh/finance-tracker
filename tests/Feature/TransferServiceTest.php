<?php

use App\Exceptions\InsufficientBalanceException;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransferService;

test('transfer moves balance from source to destination wallet', function () {
    $user = User::factory()->create();
    $fromWallet = Wallet::factory()->for($user)->create(['balance' => 1000]);
    $toWallet = Wallet::factory()->for($user)->create(['balance' => 200]);

    $transfer = app(TransferService::class)->transfer([
        'user_id' => $user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
        'transfer_date' => now()->toDateString(),
    ]);

    expect($transfer->exists)->toBeTrue();
    expect($fromWallet->fresh()->balance)->toBe(600);
    expect($toWallet->fresh()->balance)->toBe(600);
});

test('transfer throws and rolls back when source balance is insufficient', function () {
    $user = User::factory()->create();
    $fromWallet = Wallet::factory()->for($user)->create(['balance' => 100]);
    $toWallet = Wallet::factory()->for($user)->create(['balance' => 200]);

    expect(fn () => app(TransferService::class)->transfer([
        'user_id' => $user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 500,
        'transfer_date' => now()->toDateString(),
    ]))->toThrow(InsufficientBalanceException::class);

    expect($fromWallet->fresh()->balance)->toBe(100);
    expect($toWallet->fresh()->balance)->toBe(200);
});

test('cancelling a transfer reverses the balance on both wallets', function () {
    $user = User::factory()->create();
    $fromWallet = Wallet::factory()->for($user)->create(['balance' => 600]);
    $toWallet = Wallet::factory()->for($user)->create(['balance' => 600]);

    $transfer = Transfer::factory()->create([
        'user_id' => $user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
    ]);

    app(TransferService::class)->cancelTransfer($transfer->id);

    expect($fromWallet->fresh()->balance)->toBe(1000);
    expect($toWallet->fresh()->balance)->toBe(200);
    expect($transfer->fresh()->trashed())->toBeTrue();
});

test('cancelling a transfer throws and keeps the transfer when destination balance is insufficient', function () {
    $user = User::factory()->create();
    $fromWallet = Wallet::factory()->for($user)->create(['balance' => 600]);
    $toWallet = Wallet::factory()->for($user)->create(['balance' => 100]);

    $transfer = Transfer::factory()->create([
        'user_id' => $user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
    ]);

    expect(fn () => app(TransferService::class)->cancelTransfer($transfer->id))
        ->toThrow(InsufficientBalanceException::class);

    expect($fromWallet->fresh()->balance)->toBe(600);
    expect($toWallet->fresh()->balance)->toBe(100);
    expect($transfer->fresh()->trashed())->toBeFalse();
});
