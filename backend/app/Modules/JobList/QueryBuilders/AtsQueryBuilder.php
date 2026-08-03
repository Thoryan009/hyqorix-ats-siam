<?php

namespace App\Modules\JobList\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class AtsQueryBuilder
{
    /**
     * All ATS process names
     */
    protected array $atsProcesses = [
        'offer_extended',
        'visa_authorization',
        'medical_test',
        'police_clearance',
        'trade_test',
        'biometric_enrollment',
        'embassy_submission',
        'bmet_training',
        'bmet_biometric_enrollment',
        'immigration_clearance',
        'pta_request',
        'onboarding',
        'tra_process',
    ];

    /**
     * Apply ATS process-wise application counts
     */
    public function applyProcessCounts(Builder $query): Builder
    {
        $withCounts = ['applications'];

        foreach ($this->atsProcesses as $process) {
            $withCounts["applications as {$process}"] =
                fn ($q) =>
                $q->whereHas(
                    'processes.process',
                    fn ($qq) => $qq->where('name', $process)
                );
        }

        return $query->withCount($withCounts);
    }

    /**
     * Apply optional work order filter
     */
    public function applyWorkOrderFilter(
        Builder $query,
        ?string $workOrderId
    ): Builder {
        return $query->when(
            $workOrderId,
            fn ($q) => $q->where('work_order_id', $workOrderId)
        );
    }
}
 