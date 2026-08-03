<?php
namespace App\Modules\Application\Contracts;

interface JobListServiceInterface
{
    public function getTotalAmountByJobId(int $jobId, string $payer): float;
    public function getCandidateBillJobLists(): array;

    public function getJobById(int $jobId);
    public function clearJobListCache(): void;
}
