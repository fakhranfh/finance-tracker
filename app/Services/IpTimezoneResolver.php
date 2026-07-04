<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpTimezoneResolver
{
    /**
     * Resolve the IANA timezone for the given IP address using ip-api.com.
     * Returns null for private/loopback IPs (undetectable) or on failure.
     */
    public function resolve(string $ip): ?string
    {
        if (! $this->isPublicIp($ip)) {
            $ip = $this->resolveLocalDevPublicIp();

            if ($ip === null) {
                return null;
            }
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,timezone',
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('timezone');
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve timezone from IP', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * In local development, the request IP is always private/loopback, so
     * fall back to the machine's public IP (via ipify) purely so the
     * timezone-detection flow can be exercised outside of production.
     */
    private function resolveLocalDevPublicIp(): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        try {
            $response = Http::timeout(3)->get('https://api.ipify.org', ['format' => 'json']);

            if ($response->successful()) {
                return $response->json('ip');
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve local dev public IP', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
