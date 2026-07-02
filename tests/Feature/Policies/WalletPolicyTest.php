<?php

use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can manage their own wallet', function () {
    $user = User::factory()->create()->assignRole('user');
    $wallet = Wallet::factory()->for($user)->create();

    expect($user->can('view', $wallet))->toBeTrue()
        ->and($user->can('update', $wallet))->toBeTrue()
        ->and($user->can('delete', $wallet))->toBeTrue()
        ->and($user->can('create', Wallet::class))->toBeTrue();
});

test('user cannot view, update, or delete another user wallet', function () {
    $user = User::factory()->create()->assignRole('user');
    $wallet = Wallet::factory()->create();

    expect($user->can('view', $wallet))->toBeFalse()
        ->and($user->can('update', $wallet))->toBeFalse()
        ->and($user->can('delete', $wallet))->toBeFalse();
});

test('admin can view but not update or delete another user wallet', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $wallet = Wallet::factory()->create();

    expect($admin->can('view', $wallet))->toBeTrue()
        ->and($admin->can('update', $wallet))->toBeFalse()
        ->and($admin->can('delete', $wallet))->toBeFalse();
});

test('admin can fully manage their own wallet', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $wallet = Wallet::factory()->for($admin)->create();

    expect($admin->can('view', $wallet))->toBeTrue()
        ->and($admin->can('update', $wallet))->toBeTrue()
        ->and($admin->can('delete', $wallet))->toBeTrue();
});
