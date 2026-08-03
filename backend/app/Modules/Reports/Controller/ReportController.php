<?php
namespace App\Modules\Reports\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\ClientDbService;
use App\Modules\Reports\Services\CountryDbService;
use App\Modules\Reports\Services\JobListDbService;
use App\Modules\Reports\Services\WorkOrderDbService;
use App\Modules\Reports\Services\ProcessDbService;
use App\Modules\Reports\Services\ReportDataDbService;
use App\Modules\Reports\Services\TransactionDbService;
use App\Modules\Reports\Services\AgentDbService;
use App\Modules\Reports\Services\ExpiryDbService;
use App\Modules\Reports\Services\PrincipalDbService;

class ReportController extends Controller
{
    public function __construct(
        private readonly CountryDbService $countryDbService,
        private readonly ClientDbService $clientDbService,
        private readonly AgentDbService $agentDbService,
      private readonly  JobListDbService $jobListDbService,
      private readonly  WorkOrderDbService $workOrderDbService,
      private readonly  ProcessDbService $processDbService,
      private readonly  TransactionDbService $transactionDbService,
      public readonly ReportDataDbService $reportDataDbService,
        public readonly PrincipalDbService $principalDataService,
      private readonly ExpiryDbService $expiryDbService
    )
    {
    }

    public function getCountryReport()
    {
        return $this->countryDbService->getCountries();
    }

    public function getClientReport()
    {
        return $this->clientDbService->getClients();
    }

    public function getAgentReport()
    {
        return $this->agentDbService->getAgents();
    }

    public function getProcessReport()
    {
        return $this->processDbService->getProcesses();
    }

    public function getWorkOrderReport()
    {
        return $this->workOrderDbService->getWorkOrders();
    }

    public function getJobReport()
    {
        return $this->jobListDbService->getJobs();
    }

    public function getTransactionPaymentMethodReport()
    {
        return $this->transactionDbService->getPaymentMethods();
    }

     public function getTransactionStatusReport()
    {
        return $this->transactionDbService->getStatuses();
    }

     public function getReportData()
    {
        return $this->reportDataDbService->getReportData();
    }

    public function getPrincipalData()
    {
        return $this->principalDataService->getPrincipals();
    }

    public function getExpiryReport()
    {
        return $this->expiryDbService->getExpiryReport();
    }

    public function getAtsSummaryExtraData()
    {
        return $this->expiryDbService->getExpiryReportData();
    }

}
