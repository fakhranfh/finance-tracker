<?php

use App\Services\IpTimezoneResolver;
use Illuminate\Support\Facades\Http;

test('resolves the timezone for a public IP', function () {
    Http::fake([
        'ip-api.com/*' => Http::response(['status' => 'success', 'timezone' => 'Asia/Jakarta']),
    ]);

    $timezone = (new IpTimezoneResolver)->resolve('203.0.113.5');

    expect($timezone)->toBe('Asia/Jakarta');
});

test('returns null for a private or loopback IP without calling the API', function () {
    Http::fake();

    $timezone = (new IpTimezoneResolver)->resolve('127.0.0.1');

    expect($timezone)->toBeNull();
    Http::assertNothingSent();
});

test('returns null when the API responds with a failure status', function () {
    Http::fake([
        'ip-api.com/*' => Http::response(['status' => 'fail', 'message' => 'invalid query']),
    ]);

    $timezone = (new IpTimezoneResolver)->resolve('203.0.113.5');

    expect($timezone)->toBeNull();
});

test('returns null when the API request throws', function () {
    Http::fake([
        'ip-api.com/*' => Http::response('', 500),
    ]);

    $timezone = (new IpTimezoneResolver)->resolve('203.0.113.5');

    expect($timezone)->toBeNull();
});
