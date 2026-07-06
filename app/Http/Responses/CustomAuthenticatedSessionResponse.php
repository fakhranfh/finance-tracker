<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features;

class CustomAuthenticatedSessionResponse implements LoginResponse
{
    public function toResponse($request)
    {
        if (Features::enabled(Features::emailVerification())
            && $request->user()
            && is_null($request->user()->email_verified_at)) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
