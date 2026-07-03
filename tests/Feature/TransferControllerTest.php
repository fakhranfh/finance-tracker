<?php

use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
});

test('deleting a transfer reverses the balance on both wallets', function () {
    $fromWallet = Wallet::factory()->for($this->user)->create(['balance' => 600]);
    $toWallet = Wallet::factory()->for($this->user)->create(['balance' => 600]);

    $transfer = Transfer::factory()->create([
        'user_id' => $this->user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
    ]);

    $response = $this->actingAs($this->user)->deleteJson(route('transfers.destroy', $transfer));

    $response->assertOk();
    expect($fromWallet->fresh()->balance)->toBe(1000);
    expect($toWallet->fresh()->balance)->toBe(200);
    expect($transfer->fresh()->trashed())->toBeTrue();
});

test('deleting a transfer cannot be triggered by another user', function () {
    $fromWallet = Wallet::factory()->for($this->user)->create(['balance' => 600]);
    $toWallet = Wallet::factory()->for($this->user)->create(['balance' => 600]);

    $transfer = Transfer::factory()->create([
        'user_id' => $this->user->id,
        'from_wallet_id' => $fromWallet->id,
        'to_wallet_id' => $toWallet->id,
        'amount' => 400,
    ]);

    $otherUser = User::factory()->create()->assignRole('user');

    $response = $this->actingAs($otherUser)->deleteJson(route('transfers.destroy', $transfer));

    $response->assertForbidden();
    expect($fromWallet->fresh()->balance)->toBe(600);
    expect($toWallet->fresh()->balance)->toBe(600);
    expect($transfer->fresh()->trashed())->toBeFalse();
});
