<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Dusk\Browser;

function categoryUser(): User
{
    return tap(User::factory()->create(), fn (User $user) => $user->assignRole('user'));
}

function categoryAdmin(): User
{
    return tap(User::factory()->create(), fn (User $user) => $user->assignRole('admin'));
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('categories page can be rendered', function () {
    $user = categoryUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->waitForText('Expense', null, true)
            ->assertSee('No expense categories yet.')
            ->assertSee('No income categories yet.');
    });
});

test('categories page lists global and own categories but hides other users categories', function () {
    $user = categoryUser();
    Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);
    Category::factory()->for($user)->create(['name' => 'Hobi Kucing', 'type' => 'expense']);
    Category::factory()->create(['name' => 'Someone Else Category', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->waitForText('Makanan', null, true)
            ->assertSee('Hobi Kucing', true)
            ->assertDontSee('Someone Else Category')
            ->assertSee('GLOBAL');
    });
});

test('user can create an expense category from the add modal', function () {
    $user = categoryUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->waitForText('Expense', null, true)
            ->click('[onclick*="add-category-modal-expense"]')
            ->pause(300)
            ->type('#new-category-name-expense', 'Skincare')
            ->click('#add-category-modal-expense button[type="submit"]')
            ->waitForText('Category created successfully.')
            ->assertSee('Skincare', true);
    });

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Skincare',
        'type' => 'expense',
    ]);
});

test('user can create an income category from the add modal', function () {
    $user = categoryUser();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->waitForText('Income', null, true)
            ->click('[onclick*="add-category-modal-income"]')
            ->pause(300)
            ->type('#new-category-name-income', 'Gaji')
            ->click('#add-category-modal-income button[type="submit"]')
            ->waitForText('Category created successfully.')
            ->assertSee('Gaji', true);
    });

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Gaji',
        'type' => 'income',
    ]);
});

test('user can rename their own custom category from the edit modal', function () {
    $user = categoryUser();
    $category = Category::factory()->for($user)->create(['name' => 'Old Name', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($category) {
        $browser->loginAs($category->user)
            ->visit('/categories')
            ->waitForText('Old Name', null, true)
            ->click('[title="Edit category"]')
            ->pause(300)
            ->clear('name')
            ->type('name', 'New Name')
            ->press('Save Changes')
            ->waitForText('Category updated successfully.')
            ->assertSee('New Name', true);
    });

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
});

test('user can delete their own custom category from the delete modal', function () {
    $user = categoryUser();
    $category = Category::factory()->for($user)->create(['name' => 'Hobi Kucing', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($category) {
        $browser->loginAs($category->user)
            ->visit('/categories')
            ->waitForText('Hobi Kucing', null, true)
            ->click('[title="Delete category"]')
            ->pause(300)
            ->press('Delete')
            ->waitForText('Category deleted successfully.')
            ->assertSee('No expense categories yet.');
    });

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('regular user cannot see edit or delete controls on a global category', function () {
    $user = categoryUser();
    Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/categories')
            ->waitForText('Makanan', null, true)
            ->assertSee('GLOBAL')
            ->assertMissing('[title="Edit category"]')
            ->assertMissing('[title="Delete category"]');
    });
});

test('admin can edit a global category', function () {
    $admin = categoryAdmin();
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Makanan', 'type' => 'expense']);

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/categories')
            ->waitForText('Makanan', null, true)
            ->click('[title="Edit category"]')
            ->pause(300)
            ->clear('name')
            ->type('name', 'Food')
            ->press('Save Changes')
            ->waitForText('Category updated successfully.')
            ->assertSee('Food', true);
    });

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Food']);
});
