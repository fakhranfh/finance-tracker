<?php

use App\Models\User;

test('login page can be rendered', function () {
    $this->get('/login')->assertSuccessful();
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response = $this->post('/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

test('login fails with wrong password', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $this->post('/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login fails with unregistered email', function () {
    $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login is throttled after 5 failed attempts', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);
    }

    $this->post('/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ])->assertStatus(429);
});
