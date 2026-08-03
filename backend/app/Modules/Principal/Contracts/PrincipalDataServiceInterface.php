<?php
namespace App\Modules\Principal\Contracts;

interface PrincipalDataServiceInterface
{
    public function getPrincipalData(): array;
    public function clearPrincipalDataCache(): void;
}
