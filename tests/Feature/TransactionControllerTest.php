<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
});

test('index displays transactions and transfers chronologically', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['name' => 'Cash Wallet']);
    $category = Category::factory()->for($this->user)->create(['name' => 'Groceries', 'type' => 'expense']);
    Category::factory()->for($this->user)->create(['type' => 'income']);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 50_000,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('transactions.index'));

    $response->assertOk()
        ->assertSee('Cash Wallet')
        ->assertSee('Groceries');
});

test('index shows empty state when the user has no history', function () {
    Wallet::factory()->for($this->user)->create();
    Category::factory()->for($this->user)->create(['type' => 'income']);
    Category::factory()->for($this->user)->create(['type' => 'expense']);

    $response = $this->actingAs($this->user)->get(route('transactions.index'));

    $response->assertOk()->assertSee('No transactions yet');
});

test('index redirects when the user has no wallet or categories set up', function () {
    $response = $this->actingAs($this->user)->get(route('transactions.index'));

    $response->assertRedirect(route('wallets.index'))->assertSessionHasErrors('setup');
});

test('index redirects when categories are missing an income or expense type', function () {
    Wallet::factory()->for($this->user)->create();
    Category::factory()->for($this->user)->create(['type' => 'expense']);

    $response = $this->actingAs($this->user)->get(route('transactions.index'));

    $response->assertRedirect(route('wallets.index'))->assertSessionHasErrors('setup');
});

test('user can record an income transaction', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'income',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'));
    expect($wallet->fresh()->balance)->toBe(1500);
});

test('user can record an expense transaction', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'expense']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'expense',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 300,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'));
    expect($wallet->fresh()->balance)->toBe(700);
});

test('user can transfer funds between wallets', function () {
    $fromWallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $toWallet = Wallet::factory()->for($this->user)->create(['balance' => 200]);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'transfer',
        'wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'));
    expect($fromWallet->fresh()->balance)->toBe(600);
    expect($toWallet->fresh()->balance)->toBe(600);
});

test('expense request fails validation when balance is insufficient', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 100]);
    $category = Category::factory()->for($this->user)->create(['type' => 'expense']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'expense',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'))->assertSessionHasErrors('amount');
    expect($wallet->fresh()->balance)->toBe(100);
});
