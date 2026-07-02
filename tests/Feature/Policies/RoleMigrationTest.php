<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin and user roles exist right after migrating, without seeding', function () {
    expect(Role::where('name', 'admin')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Role::where('name', 'user')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and(Role::count())->toBe(2);
});

test('a user can be assigned the admin or user role created by the migration', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('user'))->toBeFalse();

    $user->syncRoles(['user']);

    expect($user->hasRole('user'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});
