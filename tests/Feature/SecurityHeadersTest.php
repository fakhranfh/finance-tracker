<?php

test('responses include hardened security headers', function () {
    $response = $this->get('/login');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
});

test('HSTS is only sent over a secure connection', function () {
    $this->get('/login')->assertHeaderMissing('Strict-Transport-Security');

    $this->get(secure_url('/login'))
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
