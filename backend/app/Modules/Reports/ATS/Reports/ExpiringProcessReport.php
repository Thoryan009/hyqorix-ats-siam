<?php

namespace App\Modules\Reports\ATS\Reports;

use App\Modules\Reports\ATS\Contracts\ATSReportContract;
use App\Modules\Reports\ATS\Repositories\ATSReportRepository;
use App\Modules\Reports\ATS\Resource\ATSResource;
class ExpiringProcessReport implements ATSReportContract
{
    public function __construct(
        protected ATSReportRepository $repository
    ) {}

    public function generate(array $filters)
    {
        $query = $this->repository->baseQuery($filters);
        $query = $this->repository->filterByDuration($query, 50, 100);

        return ATSResource::collection($query->get());
    }
        public function query(array $filters)
        {
            $query = $this->repository->baseQuery($filters);
            $query = $this->repository->filterByDuration($query, 50, 100);

            return $query; // just return QueryBuilder
        }
}
