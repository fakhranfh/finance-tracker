<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
    $this->admin = User::factory()->create()->assignRole('admin');
});

test('index displays global categories, own categories, and hides other users categories', function () {
    Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);
    Category::factory()->for($this->user)->create(['name' => 'Hobi Kucing', 'type' => 'expense']);
    Category::factory()->create(['name' => 'Someone Else Category', 'type' => 'expense']);

    $response = $this->actingAs($this->user)->get(route('categories.index'));

    $response->assertOk()
        ->assertSee('Makanan')
        ->assertSee('Hobi Kucing')
        ->assertDontSee('Someone Else Category');
});

test('index splits categories into expense and income groups', function () {
    Category::factory()->for($this->user)->create(['name' => 'Gaji', 'type' => 'income']);
    Category::factory()->for($this->user)->create(['name' => 'Skincare', 'type' => 'expense']);

    $response = $this->actingAs($this->user)->get(route('categories.index'));

    $response->assertOk()->assertSee('Gaji')->assertSee('Skincare');
});

test('regular user creates a custom category owned by themselves', function () {
    $response = $this->actingAs($this->user)->post(route('categories.store'), [
        'name' => 'Hobi Kucing',
        'type' => 'expense',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'name' => 'Hobi Kucing',
        'type' => 'expense',
    ]);
});

test('admin creates a global category with null user_id', function () {
    $response = $this->actingAs($this->admin)->post(route('categories.store'), [
        'name' => 'Makanan',
        'type' => 'expense',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', [
        'user_id' => null,
        'name' => 'Makanan',
        'type' => 'expense',
    ]);
});

test('user can rename their own custom category', function () {
    $category = Category::factory()->for($this->user)->create(['name' => 'Old Name', 'type' => 'expense']);

    $response = $this->actingAs($this->user)->put(route('categories.update', $category), [
        'name' => 'New Name',
        'type' => 'expense',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
});

test('user cannot update a global category', function () {
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);

    $this->actingAs($this->user)->put(route('categories.update', $category), [
        'name' => 'Hacked',
        'type' => 'expense',
    ])->assertForbidden();
});

test('user cannot update another user custom category', function () {
    $category = Category::factory()->create(['name' => 'Old Name', 'type' => 'expense']);

    $this->actingAs($this->user)->put(route('categories.update', $category), [
        'name' => 'Hacked',
        'type' => 'expense',
    ])->assertForbidden();
});

test('admin can update a global category', function () {
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);

    $response = $this->actingAs($this->admin)->put(route('categories.update', $category), [
        'name' => 'Food',
        'type' => 'expense',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Food']);
});

test('user can soft delete their own custom category', function () {
    $category = Category::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('user cannot delete a global category', function () {
    $category = Category::factory()->create(['user_id' => null]);

    $this->actingAs($this->user)->delete(route('categories.destroy', $category))
        ->assertForbidden();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
});
