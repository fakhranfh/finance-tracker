<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Browser::$storeScreenshotsAt = base_path('docs/dusk/images/features');

    Role::findOrCreate('admin');
    Role::findOrCreate('user');
});

/**
 * @group screenshots
 */
test('capture wallets page screenshot', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    Wallet::factory()->for($user)->create(['name' => 'Cash', 'balance' => 1500000]);
    Wallet::factory()->for($user)->create(['name' => 'Bank BCA', 'balance' => 8200000]);
    Wallet::factory()->for($user)->create(['name' => 'GoPay', 'balance' => 250000]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/wallets')
            ->pause(800)
            ->screenshot('wallets');
    });
});

test('capture categories page screenshot', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    Category::factory()->for($user)->create(['name' => 'Salary', 'type' => 'income']);
    Category::factory()->for($user)->create(['name' => 'Groceries', 'type' => 'expense']);
    Category::factory()->for($user)->create(['name' => 'Transport', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->pause(800)
            ->screenshot('categories');
    });
});

test('capture transactions page screenshot', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $wallet = Wallet::factory()->for($user)->create(['name' => 'Cash', 'balance' => 5000000]);
    $salary = Category::factory()->for($user)->create(['name' => 'Salary', 'type' => 'income']);
    $groceries = Category::factory()->for($user)->create(['name' => 'Groceries', 'type' => 'expense']);

    $entries = [
        ['category' => $salary, 'type' => 'income', 'notes' => 'Monthly salary', 'amount' => 8500000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Weekly groceries', 'amount' => 350000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Coffee with friends', 'amount' => 45000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Electricity bill', 'amount' => 275000],
        ['category' => $salary, 'type' => 'income', 'notes' => 'Freelance project payment', 'amount' => 1500000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Fuel for motorcycle', 'amount' => 100000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Internet subscription', 'amount' => 350000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Lunch at office', 'amount' => 35000],
        ['category' => $salary, 'type' => 'income', 'notes' => 'Cashback reward', 'amount' => 25000],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Movie tickets', 'amount' => 100000],
    ];

    foreach ($entries as $entry) {
        Transaction::factory()
            ->for($user)
            ->create([
                'wallet_id' => $wallet->id,
                'category_id' => $entry['category']->id,
                'type' => $entry['type'],
                'notes' => $entry['notes'],
                'amount' => $entry['amount'],
            ]);
    }

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/transactions')
            ->pause(800)
            ->screenshot('transactions');
    });
});

test('capture transfers section screenshot', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $walletA = Wallet::factory()->for($user)->create(['name' => 'Cash', 'balance' => 2000000]);
    $walletB = Wallet::factory()->for($user)->create(['name' => 'Bank BCA', 'balance' => 3000000]);

    Category::factory()->for($user)->create(['type' => 'income']);
    Category::factory()->for($user)->create(['type' => 'expense']);

    $notes = [
        'Monthly savings transfer',
        'Move funds for rent payment',
        'Top up bank account',
        'Emergency fund allocation',
        'Weekly budget transfer',
    ];

    foreach ($notes as $note) {
        Transfer::factory()
            ->for($user)
            ->create([
                'from_wallet_id' => $walletA->id,
                'to_wallet_id' => $walletB->id,
                'notes' => $note,
            ]);
    }

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/transactions')
            ->pause(800)
            ->screenshot('transfers');
    });
});

test('capture dashboard overview screenshot', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $wallet = Wallet::factory()->for($user)->create(['name' => 'Cash', 'balance' => 7500000]);
    $salary = Category::factory()->for($user)->create(['name' => 'Salary', 'type' => 'income']);
    $groceries = Category::factory()->for($user)->create(['name' => 'Groceries', 'type' => 'expense']);
    $transport = Category::factory()->for($user)->create(['name' => 'Transport', 'type' => 'expense']);
    $utilities = Category::factory()->for($user)->create(['name' => 'Utilities', 'type' => 'expense']);
    $entertainment = Category::factory()->for($user)->create(['name' => 'Entertainment', 'type' => 'expense']);

    $dayOfMonth = now()->day;
    $dayInMonth = fn (int $preferred) => now()->clone()->startOfMonth()->addDays(min($preferred, $dayOfMonth - 1))->setTime(now()->hour, now()->minute);

    $entries = [
        ['category' => $salary, 'type' => 'income', 'notes' => 'Monthly salary', 'amount' => 8500000, 'date' => $dayInMonth(0)],
        ['category' => $salary, 'type' => 'income', 'notes' => 'Freelance project payment', 'amount' => 1500000, 'date' => $dayInMonth(3)],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Weekly groceries', 'amount' => 750000, 'date' => $dayInMonth(1)],
        ['category' => $groceries, 'type' => 'expense', 'notes' => 'Weekly groceries', 'amount' => 680000, 'date' => $dayInMonth(4)],
        ['category' => $transport, 'type' => 'expense', 'notes' => 'Fuel for motorcycle', 'amount' => 300000, 'date' => $dayInMonth(2)],
        ['category' => $transport, 'type' => 'expense', 'notes' => 'Toll and parking', 'amount' => 150000, 'date' => $dayInMonth(5)],
        ['category' => $utilities, 'type' => 'expense', 'notes' => 'Electricity bill', 'amount' => 275000, 'date' => $dayInMonth(1)],
        ['category' => $utilities, 'type' => 'expense', 'notes' => 'Internet subscription', 'amount' => 350000, 'date' => $dayInMonth(3)],
        ['category' => $entertainment, 'type' => 'expense', 'notes' => 'Movie tickets', 'amount' => 120000, 'date' => $dayInMonth(2)],
        ['category' => $entertainment, 'type' => 'expense', 'notes' => 'Coffee with friends', 'amount' => 65000, 'date' => $dayInMonth(0)],
    ];

    foreach ($entries as $entry) {
        Transaction::factory()
            ->for($user)
            ->create([
                'wallet_id' => $wallet->id,
                'category_id' => $entry['category']->id,
                'type' => $entry['type'],
                'notes' => $entry['notes'],
                'amount' => $entry['amount'],
                'transaction_date' => $entry['date'],
            ]);
    }

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->pause(800)
            ->screenshot('dashboard-overview');
    });
});

test('capture roles management screenshot', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/roles')
            ->pause(800)
            ->screenshot('roles');
    });
});

test('capture users management screenshot', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->count(5)->create()->each(fn (User $u) => $u->assignRole('user'));

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/users')
            ->pause(800)
            ->screenshot('users');
    });
});
