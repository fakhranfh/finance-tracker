<?php

use App\Models\User;

test('confirm password page renders instead of crashing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk()->assertViewIs('auth.confirm-password');
});

test('unauthenticated user is redirected away from the confirm password page', function () {
    $this->get(route('password.confirm'))->assertRedirect('/login');
});
