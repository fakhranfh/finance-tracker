<?php

use App\Models\User;

test('change password page contains loading state elements', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/change-password');

    $html = $response->getContent();

    // Verify button has id for JavaScript targeting
    expect($html)->toContain('id="update-password-btn"');

    // Verify icon and text elements exist for state changes
    expect($html)->toContain('id="submit-icon"');
    expect($html)->toContain('id="submit-text"');

    // Verify loading spinner animation is defined
    expect($html)->toContain('animate-spin');
    expect($html)->toContain('@keyframes spin');

    // Verify disabled state CSS is present
    expect($html)->toContain('#update-password-btn:disabled');
    expect($html)->toContain('cursor: not-allowed');
});
