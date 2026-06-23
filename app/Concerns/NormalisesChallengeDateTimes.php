<?php

namespace App\Concerns;

use Illuminate\Support\Carbon;

/**
 * Shared by the challenge form requests to normalise the submitted start/end
 * date-times before validation.
 */
trait NormalisesChallengeDateTimes
{
    /**
     * Interpret the entered date/times in the challenge's timezone, store them
     * as UTC, and pin the start to the first second of its minute and the end
     * to the last (the form only captures times down to the minute).
     */
    protected function normaliseDateTimeSeconds(): void
    {
        $timezone = $this->input('timezone');

        if (is_string($timezone) === false || $timezone === '') {
            // Store null when no timezone is given; fall back to UTC only for
            // parsing. The required_with rule still enforces one when dates set.
            $this->merge(['timezone' => null]);

            $timezone = 'UTC';
        }

        $startsAt = $this->input('starts_at');

        if (is_string($startsAt) && $startsAt !== '') {
            $parsed = rescue(fn () => Carbon::parse($startsAt, $timezone), report: false);

            if ($parsed !== null) {
                $this->merge(['starts_at' => $parsed->utc()->startOfMinute()->toDateTimeString()]);
            }
        }

        $endsAt = $this->input('ends_at');

        if (is_string($endsAt) && $endsAt !== '') {
            $parsed = rescue(fn () => Carbon::parse($endsAt, $timezone), report: false);

            if ($parsed !== null) {
                $this->merge(['ends_at' => $parsed->utc()->endOfMinute()->toDateTimeString()]);
            }
        }

        // The timezone only applies to a schedule, so drop it when there are
        // neither a start nor an end date.
        if ($this->filled('starts_at') === false && $this->filled('ends_at') === false) {
            $this->merge(['timezone' => null]);
        }
    }
}
