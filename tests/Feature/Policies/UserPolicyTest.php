<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can view and update their own profile but not manage other accounts', function () {
    $user = User::factory()->create()->assignRole('user');
    $otherUser = User::factory()->create()->assignRole('user');

    expect($user->can('view', $user))->toBeTrue()
        ->and($user->can('update', $user))->toBeTrue()
        ->and($user->can('viewAny', User::class))->toBeFalse()
        ->and($user->can('view', $otherUser))->toBeFalse()
        ->and($user->can('update', $otherUser))->toBeFalse()
        ->and($user->can('delete', $otherUser))->toBeFalse()
        ->and($user->can('create', User::class))->toBeFalse();
});

test('admin can manage other user accounts but not delete themselves', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $otherUser = User::factory()->create()->assignRole('user');

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($admin->can('view', $otherUser))->toBeTrue()
        ->and($admin->can('update', $otherUser))->toBeTrue()
        ->and($admin->can('delete', $otherUser))->toBeTrue()
        ->and($admin->can('create', User::class))->toBeTrue()
        ->and($admin->can('delete', $admin))->toBeFalse();
});
