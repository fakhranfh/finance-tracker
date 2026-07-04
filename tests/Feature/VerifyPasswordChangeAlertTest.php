<?php

use App\Models\User;

test('password change page shows success alert after update', function () {
    $user = User::factory()->create([
        'password' => bcrypt('OldPassword@123456'),
    ]);

    // Make password update request
    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword@123456',
        'password' => 'NewPassword@654321',
        'password_confirmation' => 'NewPassword@654321',
    ]);

    // Verify redirect
    expect($response->status())->toBe(302);

    // Verify session has status message
    expect($response->getSession()->has('status'))->toBeTrue();

    // Navigate to change-password page with session
    $followResponse = $this->actingAs($user)
        ->withSession($response->getSession()->all())
        ->get('/change-password');

    // The follow response should contain the success alert
    $html = $followResponse->getContent();

    // Verify alert component is in the HTML
    expect($html)->toContain('success-alert');
    expect($html)->toContain('Success');
    expect($html)->toContain('check_circle');

    // Verify user-friendly message is displayed
    expect($html)->toContain('Your password has been changed successfully');
    expect($html)->toContain('Please remember to use your new password for future logins');

    // Verify alert styling classes are present
    expect($html)->toContain('bg-success-container');
    expect($html)->toContain('border-success');

    // Verify error alert is hidden
    expect($html)->toContain('hidden mb-6');
});
