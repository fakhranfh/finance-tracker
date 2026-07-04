<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DashboardService;

test('dashboard shows total balance summed from wallets only', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user)->create(['balance' => 100_000]);
    Wallet::factory()->for($user)->create(['balance' => 250_000]);
    Wallet::factory()->create(['balance' => 999_999]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()->assertSee('Rp 350.000');
});

test('dashboard shows month-to-date income and expense totals', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['balance' => 1_000_000]);
    $incomeCategory = Category::factory()->for($user)->create(['type' => 'income']);
    $expenseCategory = Category::factory()->for($user)->create(['type' => 'expense']);

    Transaction::factory()->for($user)->for($wallet)->create([
        'category_id' => $incomeCategory->id,
        'type' => 'income',
        'amount' => 500_000,
        'transaction_date' => now(),
    ]);

    Transaction::factory()->for($user)->for($wallet)->create([
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 200_000,
        'transaction_date' => now(),
    ]);

    Transaction::factory()->for($user)->for($wallet)->create([
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 999_000,
        'transaction_date' => now()->subMonth(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Rp 500.000')
        ->assertSee('Rp 200.000');

    $totalExpenseThisMonth = app(DashboardService::class)->getMonthToDateCashFlow($user->id)['expense'];
    expect($totalExpenseThisMonth)->toBe(200_000);
});

test('dashboard lists recent transactions for the authenticated user only', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['type' => 'expense']);

    Transaction::factory()->for($user)->for($wallet)->create([
        'category_id' => $category->id,
        'type' => 'expense',
        'transaction_date' => now(),
    ]);

    $otherUser = User::factory()->create();
    $otherWallet = Wallet::factory()->for($otherUser)->create(['name' => 'Someone Else Wallet']);
    Transaction::factory()->for($otherUser)->for($otherWallet)->create([
        'category_id' => Category::factory()->for($otherUser)->create(['type' => 'expense']),
        'type' => 'expense',
        'transaction_date' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee($wallet->name)
        ->assertDontSee('Someone Else Wallet');
});
