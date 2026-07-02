<?php

use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Dusk\Browser;

function walletUser(): User
{
    return tap(User::factory()->create(), fn (User $user) => $user->assignRole('user'));
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('wallets page can be rendered', function () {
    $user = walletUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/wallets')
            ->waitForText('Total Consolidated Balance', null, true)
            ->assertSee('No wallets yet?');
    });
});

test('wallets page lists the user wallets and total balance', function () {
    $user = walletUser();
    Wallet::factory()->for($user)->create(['name' => 'Cash Wallet', 'balance' => 100_000]);
    Wallet::factory()->for($user)->create(['name' => 'Bank Account', 'balance' => 250_000]);
    Wallet::factory()->create(['name' => 'Someone Else Wallet']);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/wallets')
            ->waitForText('Cash Wallet', null, true)
            ->assertSee('Bank Account', true)
            ->assertDontSee('Someone Else Wallet')
            ->assertSee('Rp 350.000');
    });
});

test('user can create a wallet from the add wallet modal', function () {
    $user = walletUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/wallets')
            ->waitForText('Total Consolidated Balance', null, true)
            ->press('Add Wallet')
            ->pause(300)
            ->type('name', 'Mandiri Account')
            ->type('balance', '500000')
            ->press('Create Wallet')
            ->waitForText('Wallet created successfully.')
            ->assertSee('Mandiri Account', true)
            ->assertSee('Rp 500.000');
    });

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'name' => 'Mandiri Account',
        'balance' => 500_000,
    ]);
});

test('user can rename a wallet from the edit wallet modal', function () {
    $user = walletUser();
    $wallet = Wallet::factory()->for($user)->create(['name' => 'Old Name']);

    $this->browse(function (Browser $browser) use ($wallet) {
        $browser->loginAs($wallet->user)
            ->visit('/wallets')
            ->waitForText('Old Name', null, true)
            ->click('[title="Edit wallet"]')
            ->pause(300)
            ->clear('name')
            ->type('name', 'New Name')
            ->press('Save Changes')
            ->waitForText('Wallet updated successfully.')
            ->assertSee('New Name', true);
    });

    $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'name' => 'New Name']);
});

test('user can delete a wallet from the delete wallet modal', function () {
    $user = walletUser();
    $wallet = Wallet::factory()->for($user)->create(['name' => 'Cash Wallet']);

    $this->browse(function (Browser $browser) use ($wallet) {
        $browser->loginAs($wallet->user)
            ->visit('/wallets')
            ->waitForText('Cash Wallet', null, true)
            ->click('[title="Delete wallet"]')
            ->pause(300)
            ->press('Delete')
            ->waitForText('Wallet deleted successfully.')
            ->assertSee('No wallets yet?');
    });

    $this->assertSoftDeleted('wallets', ['id' => $wallet->id]);
});
