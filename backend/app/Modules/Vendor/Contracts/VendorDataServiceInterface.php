<?php

namespace App\Modules\Vendor\Contracts;

interface VendorDataServiceInterface
{
    public function getVendorData(): array;

    public function clearVendorDataCache(): void;
}
