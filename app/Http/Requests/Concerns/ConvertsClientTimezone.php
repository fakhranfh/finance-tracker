<?php

namespace App\Http\Requests\Concerns;

use Carbon\Carbon;

trait ConvertsClientTimezone
{
    /**
     * Convert the given naive datetime fields from the client's local
     * timezone (read from the `client_timezone` cookie set globally by
     * the app layout) into the application's timezone, in place.
     *
     * @param  array<int, string>  $fields
     */
    protected function convertFieldsToAppTimezone(array $fields): void
    {
        $clientTimezone = $this->cookie('client_timezone');

        if (! $clientTimezone) {
            return;
        }

        $converted = [];

        foreach ($fields as $field) {
            if (! $this->filled($field)) {
                continue;
            }

            try {
                $converted[$field] = Carbon::parse($this->input($field), $clientTimezone)
                    ->setTimezone(config('app.timezone'))
                    ->toDateTimeString();
            } catch (\Exception) {
                continue;
            }
        }

        $this->merge($converted);
    }
}
