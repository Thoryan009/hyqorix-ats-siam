<?php
namespace App\Modules\Auth\Services;
use App\Modules\Auth\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(
        protected DashboardRepository $repository
    ) {}

    public function getDashboardData(): array
    {
        return [
            'summary' => $this->repository->getSummary(),
            'work_orders' => $this->repository->getWorkOrderSummary(),
            'jobs' => $this->repository->getJobSummary(),
            'pipeline' => $this->repository->getProcessPipeline(),
            'flight_summary' => $this->repository->getFlightSummary(10, ['upcoming' => true]),
            'finance' => [],
            'payment_methods' => $this->repository->getPaymentMethodSummary(),
            'client_billing' => $this->repository->getClientBillingSummary(),
            'recent_applications' => $this->repository->getRecentApplications(),
            'recent_transactions' => $this->repository->getRecentTransactions(),
            'recent_work_orders' => $this->repository->getRecentWorkOrders(),
            'alerts' => $this->repository->getAlerts(),
        ];
    }

    public function getFlightSummary(array $filters = []): array
    {
        return $this->repository->getFlightSummary(null, $filters);
    }
}
