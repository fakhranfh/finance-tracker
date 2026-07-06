<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    if (! Features::enabled(Features::emailVerification())) {
        $this->markTestSkipped('Email verification feature is disabled (set EMAIL_VERIFICATION_ENABLED=true to re-enable).');
    }
});

test('email verification notice page can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertSuccessful();
});

test('user can verify email with valid signed url', function () {
    Event::fake();

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/dashboard?verified=1');

    $this->assertTrue($user->fresh()->hasVerifiedEmail());

    Event::assertDispatched(Verified::class);
});

test('verified user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful();
});

test('unverified user is redirected from protected routes', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});

test('unverified admin is not required to verify email', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->unverified()->create()->assignRole('admin');

    $this->assertTrue($user->hasVerifiedEmail());

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful();
});

test('verification notification can be resent', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('/email/verification-notification')
        ->assertRedirect()
        ->assertSessionHas('status', 'verification-link-sent');
});
