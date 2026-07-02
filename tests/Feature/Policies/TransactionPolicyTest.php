<?php

use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can manage their own transaction', function () {
    $user = User::factory()->create()->assignRole('user');
    $transaction = Transaction::factory()->for($user)->create();

    expect($user->can('view', $transaction))->toBeTrue()
        ->and($user->can('update', $transaction))->toBeTrue()
        ->and($user->can('delete', $transaction))->toBeTrue()
        ->and($user->can('create', Transaction::class))->toBeTrue();
});

test('user cannot view, update, or delete another user transaction', function () {
    $user = User::factory()->create()->assignRole('user');
    $transaction = Transaction::factory()->create();

    expect($user->can('view', $transaction))->toBeFalse()
        ->and($user->can('update', $transaction))->toBeFalse()
        ->and($user->can('delete', $transaction))->toBeFalse();
});

test('admin can view but not update or delete another user transaction', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $transaction = Transaction::factory()->create();

    expect($admin->can('view', $transaction))->toBeTrue()
        ->and($admin->can('update', $transaction))->toBeFalse()
        ->and($admin->can('delete', $transaction))->toBeFalse();
});

test('admin can fully manage their own transaction', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $transaction = Transaction::factory()->for($admin)->create();

    expect($admin->can('view', $transaction))->toBeTrue()
        ->and($admin->can('update', $transaction))->toBeTrue()
        ->and($admin->can('delete', $transaction))->toBeTrue();
});
