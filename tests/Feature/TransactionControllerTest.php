<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
});

test('index renders the transaction history table shell', function () {
    Wallet::factory()->for($this->user)->create(['name' => 'Cash Wallet']);
    Category::factory()->for($this->user)->create(['type' => 'expense']);
    Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->get(route('transactions.index'));

    $response->assertOk()
        ->assertSee('Transaction History')
        ->assertSee('Cash Wallet');
});

test('data endpoint returns transactions and transfers sorted by date descending by default', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['name' => 'Cash Wallet']);
    $category = Category::factory()->for($this->user)->create(['name' => 'Groceries', 'type' => 'expense']);
    Category::factory()->for($this->user)->create(['type' => 'income']);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 50_000,
        'notes' => null,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->getJson(route('transactions.data'));

    $response->assertOk();
    expect($response->json('data.0.description'))->toBe('Groceries');
    expect($response->json('data.0.wallet_label'))->toBe('Cash Wallet');
    expect($response->json('data.0.amount'))->toBe(50_000);
});

test('data endpoint returns an empty list when the user has no history', function () {
    Wallet::factory()->for($this->user)->create();
    Category::factory()->for($this->user)->create(['type' => 'income']);
    Category::factory()->for($this->user)->create(['type' => 'expense']);

    $response = $this->actingAs($this->user)->getJson(route('transactions.data'));

    $response->assertOk()->assertJson(['data' => []]);
});

test('data endpoint sorts by amount ascending when requested', function () {
    $wallet = Wallet::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['type' => 'expense']);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 500,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('transactions.data', ['sort' => 'amount', 'dir' => 'asc']));

    $response->assertOk();
    expect($response->json('data.*.amount'))->toBe([100, 500]);
});

test('data endpoint filters by wallet', function () {
    $matchingWallet = Wallet::factory()->for($this->user)->create();
    $otherWallet = Wallet::factory()->for($this->user)->create();
    $category = Category::factory()->for($this->user)->create(['type' => 'expense']);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $matchingWallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 500,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $otherWallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('transactions.data', ['wallet_id' => $matchingWallet->id]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.amount'))->toBe(500);
});

test('data endpoint filters by type', function () {
    $wallet = Wallet::factory()->for($this->user)->create();
    $expenseCategory = Category::factory()->for($this->user)->create(['type' => 'expense']);
    $incomeCategory = Category::factory()->for($this->user)->create(['type' => 'income']);
    $otherWallet = Wallet::factory()->for($this->user)->create();

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $expenseCategory->id,
        'type' => 'expense',
        'amount' => 100,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $incomeCategory->id,
        'type' => 'income',
        'amount' => 200,
    ]);

    Transfer::factory()->create([
        'user_id' => $this->user->id,
        'from_wallet_id' => $wallet->id,
        'to_wallet_id' => $otherWallet->id,
        'amount' => 300,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('transactions.data', ['type' => 'expense']));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.kind'))->toBe('expense');
    expect($response->json('data.0.amount'))->toBe(100);

    $transferResponse = $this->actingAs($this->user)->getJson(route('transactions.data', ['type' => 'transfer']));

    $transferResponse->assertOk();
    expect($transferResponse->json('data'))->toHaveCount(1);
    expect($transferResponse->json('data.0.kind'))->toBe('transfer');
    expect($transferResponse->json('data.0.amount'))->toBe(300);
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

test('transaction request rejects a category whose type does not match the transaction type', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $incomeCategory = Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'expense',
        'wallet_id' => $wallet->id,
        'category_id' => $incomeCategory->id,
        'amount' => 300,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('category_id');
    expect($wallet->fresh()->balance)->toBe(1000);
});

test('transaction request rejects an amount above the allowed ceiling', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'income',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 9223372036854775807,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('amount');
    expect($wallet->fresh()->balance)->toBe(1000);
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

test('transaction date is converted from the user\'s stored timezone to the app timezone', function () {
    $this->user->forceFill(['timezone' => 'Asia/Jakarta'])->save();

    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    // A fixed past moment expressed in Asia/Jakarta (UTC+7) local time.
    $clientLocalDate = now('Asia/Jakarta')->subDay()->setTime(23, 30)->format('Y-m-d H:i:s');

    $response = $this->actingAs($this->user)
        ->post(route('transactions.store'), [
            'type' => 'income',
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 500,
            'transaction_date' => $clientLocalDate,
        ]);

    $response->assertRedirect(route('transactions.index'));

    $transaction = $wallet->transactions()->latest()->first();

    expect($transaction->transaction_date->format('H:i'))
        ->toBe(now('Asia/Jakarta')->subDay()->setTime(23, 30)->setTimezone(config('app.timezone'))->format('H:i'));
});

test('transaction request fails validation when the date is in the future', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'income',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'transaction_date' => now()->addDay()->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors('transaction_date');
    expect($wallet->fresh()->balance)->toBe(1000);
});

test('transaction request fails validation when the time is in the future on today\'s date', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'type' => 'income',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
        'transaction_date' => now()->addHour()->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors('transaction_date');
    expect($wallet->fresh()->balance)->toBe(1000);
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

    $response->assertRedirect(route('transactions.create'))->assertSessionHasErrors('amount');
    expect($wallet->fresh()->balance)->toBe(100);
});

test('deleting an expense transaction restores the wallet balance', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 700]);
    $category = Category::factory()->for($this->user)->create(['type' => 'expense']);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 300,
    ]);

    $response = $this->actingAs($this->user)->deleteJson(route('transactions.destroy', $transaction));

    $response->assertOk();
    expect($wallet->fresh()->balance)->toBe(1000);
    expect($transaction->fresh()->trashed())->toBeTrue();
});

test('deleting an income transaction cannot be triggered by another user', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['balance' => 1000]);
    $category = Category::factory()->for($this->user)->create(['type' => 'income']);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 500,
    ]);

    $otherUser = User::factory()->create()->assignRole('user');

    $response = $this->actingAs($otherUser)->deleteJson(route('transactions.destroy', $transaction));

    $response->assertForbidden();
    expect($wallet->fresh()->balance)->toBe(1000);
    expect($transaction->fresh()->trashed())->toBeFalse();
});
