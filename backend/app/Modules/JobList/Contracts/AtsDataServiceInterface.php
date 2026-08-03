<?php

namespace App\Modules\JobList\Contracts;
use Illuminate\Http\Request;

interface AtsDataServiceInterface
{
    public function getAtsData(array $filters, array $agentJobIds = []);
    public function flushCache();
    public function deleteCurrentApplicationProcess($applicationProcessId);

}
