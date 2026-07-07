<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

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

test('admin can reset another user password', function () {
    $response = $this->actingAs($this->admin)->put(route('users.reset-password', $this->user), [
        'password' => 'NewSecret!Pass123#Secure',
        'password_confirmation' => 'NewSecret!Pass123#Secure',
    ]);

    $response->assertRedirect(route('users.index'))->assertSessionHas('success');
    expect(Hash::check('NewSecret!Pass123#Secure', $this->user->fresh()->password))->toBeTrue();
});

test('admin resetting password requires confirmation match', function () {
    $response = $this->actingAs($this->admin)->put(route('users.reset-password', $this->user), [
        'password' => 'NewSecret!Pass123#Secure',
        'password_confirmation' => 'Mismatch!Pass123#Secure',
    ]);

    $response->assertSessionHasErrors('password');
});

test('regular user cannot reset another user password', function () {
    $other = User::factory()->create()->assignRole('user');

    $this->actingAs($this->user)->put(route('users.reset-password', $other), [
        'password' => 'NewSecret!Pass123#Secure',
        'password_confirmation' => 'NewSecret!Pass123#Secure',
    ])->assertForbidden();
});

test('admin cannot reset their own password via the admin reset endpoint', function () {
    $response = $this->actingAs($this->admin)->put(route('users.reset-password', $this->admin), [
        'password' => 'NewSecret!Pass123#Secure',
        'password_confirmation' => 'NewSecret!Pass123#Secure',
    ]);

    $response->assertRedirect(route('users.index'))->assertSessionHasErrors('password');
    expect(Hash::check('NewSecret!Pass123#Secure', $this->admin->fresh()->password))->toBeFalse();
});
