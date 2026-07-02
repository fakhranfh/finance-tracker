<?php

use App\Models\Transfer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can manage their own transfer', function () {
    $user = User::factory()->create()->assignRole('user');
    $transfer = Transfer::factory()->for($user)->create();

    expect($user->can('view', $transfer))->toBeTrue()
        ->and($user->can('update', $transfer))->toBeTrue()
        ->and($user->can('delete', $transfer))->toBeTrue()
        ->and($user->can('create', Transfer::class))->toBeTrue();
});

test('user cannot view, update, or delete another user transfer', function () {
    $user = User::factory()->create()->assignRole('user');
    $transfer = Transfer::factory()->create();

    expect($user->can('view', $transfer))->toBeFalse()
        ->and($user->can('update', $transfer))->toBeFalse()
        ->and($user->can('delete', $transfer))->toBeFalse();
});

test('admin can view but not update or delete another user transfer', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $transfer = Transfer::factory()->create();

    expect($admin->can('view', $transfer))->toBeTrue()
        ->and($admin->can('update', $transfer))->toBeFalse()
        ->and($admin->can('delete', $transfer))->toBeFalse();
});

test('admin can fully manage their own transfer', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $transfer = Transfer::factory()->for($admin)->create();

    expect($admin->can('view', $transfer))->toBeTrue()
        ->and($admin->can('update', $transfer))->toBeTrue()
        ->and($admin->can('delete', $transfer))->toBeTrue();
});
