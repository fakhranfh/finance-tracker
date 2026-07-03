<?php

use App\Exceptions\InsufficientBalanceException;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransactionService;

test('income increases wallet balance', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 1000]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    $transaction = app(TransactionService::class)->record([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'type' => 'income',
        'transaction_date' => now()->toDateString(),
    ]);

    expect($transaction->exists)->toBeTrue();
    expect($wallet->fresh()->balance)->toBe(1500);
});

test('expense decreases wallet balance', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 1000]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    app(TransactionService::class)->record([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 300,
        'type' => 'expense',
        'transaction_date' => now()->toDateString(),
    ]);

    expect($wallet->fresh()->balance)->toBe(700);
});

test('history page data reports each transaction kind as its plain string type', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 1000]);
    $incomeCategory = Category::factory()->for($user)->create(['type' => 'income']);
    $expenseCategory = Category::factory()->for($user)->create(['type' => 'expense']);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $incomeCategory->id,
        'type' => 'income',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
    ]);

    $history = app(TransactionService::class)->getHistoryPageData($user->id)['history'];

    expect($history->pluck('kind')->all())->toEqualCanonicalizing(['income', 'expense']);
});

test('expense throws and rolls back when balance is insufficient', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    expect(fn () => app(TransactionService::class)->record([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'type' => 'expense',
        'transaction_date' => now()->toDateString(),
    ]))->toThrow(InsufficientBalanceException::class);

    expect($wallet->fresh()->balance)->toBe(100);
    expect($wallet->fresh()->transactions()->count())->toBe(0);
});

test('cancelling an expense adds the amount back to the wallet balance', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 700]);
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 300,
    ]);

    app(TransactionService::class)->cancel($transaction->id);

    expect($wallet->fresh()->balance)->toBe(1000);
    expect($transaction->fresh()->trashed())->toBeTrue();
});

test('cancelling an income subtracts the amount from the wallet balance', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 1500]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 500,
    ]);

    app(TransactionService::class)->cancel($transaction->id);

    expect($wallet->fresh()->balance)->toBe(1000);
    expect($transaction->fresh()->trashed())->toBeTrue();
});

test('cancelling an income throws and keeps the transaction when balance would go negative', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 100]);
    $category = Category::factory()->for($user)->create(['type' => 'income']);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 500,
    ]);

    expect(fn () => app(TransactionService::class)->cancel($transaction->id))
        ->toThrow(InsufficientBalanceException::class);

    expect($wallet->fresh()->balance)->toBe(100);
    expect($transaction->fresh()->trashed())->toBeFalse();
});
