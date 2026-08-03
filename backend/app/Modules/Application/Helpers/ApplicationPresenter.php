<?php

namespace App\Modules\Application\Helpers;


class ApplicationPresenter
{
    public static function fullName($surName, $givenName): ?string
    {
        return trim($surName . ' ' . $givenName);
    }
}
