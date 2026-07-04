<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
    $this->admin = User::factory()->create()->assignRole('admin');
});

test('admin can view the users list', function () {
    $response = $this->actingAs($this->admin)->get(route('users.index'));

    $response->assertOk()->assertSee($this->user->email)->assertSee($this->admin->email);
});

test('regular user cannot view the users list', function () {
    $this->actingAs($this->user)->get(route('users.index'))->assertForbidden();
});

test('admin can change another user role', function () {
    $response = $this->actingAs($this->admin)->put(route('users.update-role', $this->user), [
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('users.index'));
    expect($this->user->fresh()->hasRole('admin'))->toBeTrue();
    expect($this->user->fresh()->hasRole('user'))->toBeFalse();
});

test('admin cannot change their own role', function () {
    $response = $this->actingAs($this->admin)->put(route('users.update-role', $this->admin), [
        'role' => 'user',
    ]);

    $response->assertRedirect(route('users.index'));
    expect($this->admin->fresh()->hasRole('admin'))->toBeTrue();
});

test('regular user cannot change another user role', function () {
    $other = User::factory()->create()->assignRole('user');

    $this->actingAs($this->user)->put(route('users.update-role', $other), [
        'role' => 'admin',
    ])->assertForbidden();
});
