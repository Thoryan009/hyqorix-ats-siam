<?php

namespace App\Modules\Shared\Helpers;

use Carbon\Carbon;

class DateTimeFormatter
{
    /**
     * Format a datetime in "Fri, 2 Jan, 26 -> 10:00 AM" style
     */
    public static function formatDateTime($dateTime): ?string
    {
        if (!$dateTime) return null;

        return $dateTime
            ->timezone(config('app.timezone'))
            ->format('D, j M, y - h:i A');
    }


    /**
     * Optional: Just date
     */
    public static function formatDate($dateTime): ?string
    {
        if (!$dateTime) return null;

        return Carbon::parse($dateTime)->format('D, j M, y');
    }

    /**
     * Optional: Just time
     */
    public static function formatTime($dateTime): ?string
    {
        if (!$dateTime) return null;

        return Carbon::parse($dateTime)->format('h:i A');
    }
}
