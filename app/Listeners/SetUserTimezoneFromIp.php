<?php

namespace App\Listeners;

use App\Services\IpTimezoneResolver;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class SetUserTimezoneFromIp
{
    public function __construct(
        protected IpTimezoneResolver $timezoneResolver,
        protected Request $request,
    ) {}

    public function handle(Login $event): void
    {
        if ($event->user->timezone !== null) {
            return;
        }

        $timezone = $this->timezoneResolver->resolve($this->request->ip());

        if ($timezone === null) {
            return;
        }

        $event->user->forceFill(['timezone' => $timezone])->save();
    }
}
