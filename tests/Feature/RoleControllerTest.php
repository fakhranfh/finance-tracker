<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
    $this->admin = User::factory()->create()->assignRole('admin');
});

test('admin can view the roles list', function () {
    $response = $this->actingAs($this->admin)->get(route('roles.index'));

    $response->assertOk()->assertSee('admin')->assertSee('user');
});

test('regular user cannot view the roles list', function () {
    $this->actingAs($this->user)->get(route('roles.index'))->assertForbidden();
});

test('admin can create a role with permissions', function () {
    $response = $this->actingAs($this->admin)->post(route('roles.store'), [
        'name' => 'auditor',
        'permissions' => ['view-transaction', 'view-wallet'],
    ]);

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseHas('roles', ['name' => 'auditor']);

    $role = Role::findByName('auditor');
    expect($role->permissions->pluck('name')->all())->toEqualCanonicalizing(['view-transaction', 'view-wallet']);
});

test('regular user cannot create a role', function () {
    $this->actingAs($this->user)->post(route('roles.store'), [
        'name' => 'auditor',
    ])->assertForbidden();
});

test('admin can update a role name and its permissions', function () {
    $role = Role::findOrCreate('auditor');
    $role->syncPermissions(['view-wallet']);

    $response = $this->actingAs($this->admin)->put(route('roles.update', $role), [
        'name' => 'senior-auditor',
        'permissions' => ['view-transaction'],
    ]);

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'senior-auditor']);

    expect($role->fresh()->permissions->pluck('name')->all())->toEqualCanonicalizing(['view-transaction']);
});

test('admin can delete a custom role', function () {
    $role = Role::findOrCreate('auditor');

    $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $role));

    $response->assertRedirect(route('roles.index'));
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('admin cannot delete the protected admin or user role', function () {
    $adminRole = Role::findByName('admin');
    $userRole = Role::findByName('user');

    $this->actingAs($this->admin)->delete(route('roles.destroy', $adminRole))->assertForbidden();
    $this->actingAs($this->admin)->delete(route('roles.destroy', $userRole))->assertForbidden();

    $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    $this->assertDatabaseHas('roles', ['id' => $userRole->id]);
});

test('admin cannot update the protected admin or user role', function () {
    $adminRole = Role::findByName('admin');
    $userRole = Role::findByName('user');
    $originalUserPermissions = $userRole->permissions->pluck('name')->all();

    $this->actingAs($this->admin)->put(route('roles.update', $adminRole), [
        'name' => 'admin',
        'permissions' => ['view-transaction'],
    ])->assertForbidden();

    $this->actingAs($this->admin)->put(route('roles.update', $userRole), [
        'name' => 'user',
        'permissions' => array_merge($originalUserPermissions, ['update-user']),
    ])->assertForbidden();

    expect($userRole->fresh()->permissions->pluck('name')->all())->toEqualCanonicalizing($originalUserPermissions);
});
