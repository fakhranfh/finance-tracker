<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can manage their own category', function () {
    $user = User::factory()->create()->assignRole('user');
    $category = Category::factory()->for($user)->create();

    expect($user->can('view', $category))->toBeTrue()
        ->and($user->can('update', $category))->toBeTrue()
        ->and($user->can('delete', $category))->toBeTrue()
        ->and($user->can('create', Category::class))->toBeTrue();
});

test('user cannot view, update, or delete another user category', function () {
    $user = User::factory()->create()->assignRole('user');
    $category = Category::factory()->create();

    expect($user->can('view', $category))->toBeFalse()
        ->and($user->can('update', $category))->toBeFalse()
        ->and($user->can('delete', $category))->toBeFalse();
});

test('admin can view but not update or delete another user category', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $category = Category::factory()->create();

    expect($admin->can('view', $category))->toBeTrue()
        ->and($admin->can('update', $category))->toBeFalse()
        ->and($admin->can('delete', $category))->toBeFalse();
});

test('admin can fully manage their own category', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $category = Category::factory()->for($admin)->create();

    expect($admin->can('view', $category))->toBeTrue()
        ->and($admin->can('update', $category))->toBeTrue()
        ->and($admin->can('delete', $category))->toBeTrue();
});
