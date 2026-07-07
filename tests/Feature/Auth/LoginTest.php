<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

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

test('login detects and stores the user timezone from their IP when not already set', function () {
    Http::fake([
        'ip-api.com/*' => Http::response(['status' => 'success', 'timezone' => 'Asia/Jakarta']),
    ]);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password',
        'timezone' => null,
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])->post('/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    expect($user->fresh()->timezone)->toBe('Asia/Jakarta');
});

test('login does not overwrite an already-known user timezone', function () {
    Http::fake([
        'ip-api.com/*' => Http::response(['status' => 'success', 'timezone' => 'Asia/Jakarta']),
    ]);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password',
        'timezone' => 'America/New_York',
    ]);

    $this->post('/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    expect($user->fresh()->timezone)->toBe('America/New_York');
    Http::assertNothingSent();
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

test('login throttling cannot be bypassed by spoofing X-Forwarded-For', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->withHeaders(['X-Forwarded-For' => "198.51.100.{$i}"])->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);
    }

    $this->withHeaders(['X-Forwarded-For' => '198.51.100.99'])->post('/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
    ])->assertStatus(429);
});
