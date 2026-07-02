<?php

use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
});

test('index displays the authenticated user wallets and total balance', function () {
    Wallet::factory()->for($this->user)->create(['name' => 'Cash Wallet', 'balance' => 100_000]);
    Wallet::factory()->for($this->user)->create(['name' => 'Bank Account', 'balance' => 250_000]);
    Wallet::factory()->create(['name' => 'Someone Else Wallet']);

    $response = $this->actingAs($this->user)->get(route('wallets.index'));

    $response->assertOk()
        ->assertSee('Cash Wallet')
        ->assertSee('Bank Account')
        ->assertDontSee('Someone Else Wallet')
        ->assertSee('Rp 350.000');
});

test('index shows empty state when the user has no wallets', function () {
    $response = $this->actingAs($this->user)->get(route('wallets.index'));

    $response->assertOk()->assertSee('No wallets yet?');
});

test('user can create a wallet', function () {
    $response = $this->actingAs($this->user)->post(route('wallets.store'), [
        'name' => 'Mandiri Account',
        'balance' => 500_000,
    ]);

    $response->assertRedirect(route('wallets.index'));
    $this->assertDatabaseHas('wallets', [
        'user_id' => $this->user->id,
        'name' => 'Mandiri Account',
        'balance' => 500_000,
    ]);
});

test('user can rename their own wallet', function () {
    $wallet = Wallet::factory()->for($this->user)->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->user)->put(route('wallets.update', $wallet), [
        'name' => 'New Name',
    ]);

    $response->assertRedirect(route('wallets.index'));
    $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'name' => 'New Name']);
});

test('user cannot update another user wallet', function () {
    $wallet = Wallet::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->user)->put(route('wallets.update', $wallet), ['name' => 'Hacked'])
        ->assertForbidden();
});

test('user can soft delete their own wallet', function () {
    $wallet = Wallet::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->delete(route('wallets.destroy', $wallet));

    $response->assertRedirect(route('wallets.index'));
    $this->assertSoftDeleted('wallets', ['id' => $wallet->id]);
});

test('user cannot delete another user wallet', function () {
    $wallet = Wallet::factory()->create();

    $this->actingAs($this->user)->delete(route('wallets.destroy', $wallet))
        ->assertForbidden();

    $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'deleted_at' => null]);
});
