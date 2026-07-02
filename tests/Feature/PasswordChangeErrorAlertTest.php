<?php

use App\Models\User;

test('when validation fails, user is redirected to change password page (not home)', function () {
    $user = User::factory()->create([
        'password' => bcrypt('OldPassword@123456'),
    ]);

    // Incorrect current password
    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'WrongPassword@123456',
        'password' => 'NewPassword@654321',
        'password_confirmation' => 'NewPassword@654321',
    ]);

    // Should redirect to /change-password (not home)
    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toBe(url('/change-password'));
});

test('weak password validation redirects to change password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('OldPassword@123456'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword@123456',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    // Should redirect to /change-password
    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toBe(url('/change-password'));
});

test('password confirmation mismatch redirects to change password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('OldPassword@123456'),
    ]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword@123456',
        'password' => 'NewPassword@654321',
        'password_confirmation' => 'DifferentPassword@654321',
    ]);

    // Should redirect to /change-password
    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toBe(url('/change-password'));
});
