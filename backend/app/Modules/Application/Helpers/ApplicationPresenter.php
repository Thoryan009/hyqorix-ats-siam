<?php

namespace App\Modules\Application\Helpers;


class ApplicationPresenter
{
    public static function fullName($surName, $givenName): ?string
    {
        return trim($surName . ' ' . $givenName);
    }

    /**
     * BD local mobile for reports: drop +880 / 880 and keep 01XXXXXXXXX.
     */
    public static function localMobile(?string $mobile): string
    {
        $value = trim((string) $mobile);
        if ($value === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return $value;
        }

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }
}
