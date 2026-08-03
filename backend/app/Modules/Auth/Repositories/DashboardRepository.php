<?php

namespace App\Modules\Auth\Repositories;
use App\Modules\Auth\Models\User;
use App\Modules\Client\Models\Client;
use App\Modules\Agent\Models\Agent;
use App\Modules\Employee\Models\Employee;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\JobList\Models\JobList;
use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\Transaction;
use App\Modules\Application\Models\ClientTransaction;
use App\Modules\Application\Models\Process;
use Illuminate\Support\Facades\DB;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class DashboardRepository
{
    // done
    public function getSummary()
    {
        $user = auth()->user();
        $jobIds = [];
        $applicationIds = [];
        if ($user && $user->type === 'agent') {
            $jobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
            $applicationIds = $user->agent?->applications()->pluck('id')->toArray() ?? [];
        } elseif ($user && $user->type === 'client') {
            $jobIds = JobList::whereHas('workOrder', function ($query) use ($user) {
                $query->where('client_id', $user->client->id);
            })
                ->pluck('id')
                ->toArray();
            $applicationIds = Application::whereHas('jobList.workOrder', function ($query) use ($user) {
                $query->where('client_id', $user->client->id);
            })
                ->pluck('id')
                ->toArray();
        }

        return [
            'total_users' => User::count(),
            'total_clients' => Client::count(),
            'total_agents' => Agent::count(),
            'total_employees' => Employee::count(),

            'total_work_orders' => WorkOrder::count(),
            'total_jobs' => JobList::when(!empty($jobIds), function ($query) use ($jobIds) {
                $query->whereIn('id', $jobIds);
            })->count(),
            'total_applications' => Application::when(!empty($applicationIds), function ($query) use ($applicationIds) {
                $query->whereIn('id', $applicationIds);
            })->count(),

            'total_revenue' => Transaction::sum('paid_amount'),
        ];
    }

    public function getWorkOrderSummary()
    {
        return [
            'total' => WorkOrder::count(),
            'active' => WorkOrder::whereDate('end_date', '>=', now())->count(),
            'expired' => WorkOrder::whereDate('end_date', '<', now())->count(),
            'total_candidates' => WorkOrder::sum('candidates'),
        ];
    }

    public function getJobSummary()
    {
        $user = auth()->user();
        $jobIds = [];
        if ($user && $user->type === 'agent') {
            $jobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
        } elseif ($user && $user->type === 'client') {
            $jobIds = JobList::whereHas('workOrder', function ($query) use ($user) {
                $query->where('client_id', $user->client->id);
            })
                ->pluck('id')
                ->toArray();
        }
        return [
            'open' => JobList::when(!empty($jobIds), function ($query) use ($jobIds) {
                $query->whereIn('id', $jobIds);
            })
                ->where('status', 'open')
                ->count(),
            'closed' => JobList::when(!empty($jobIds), function ($query) use ($jobIds) {
                $query->whereIn('id', $jobIds);
            })
                ->where('status', 'closed')
                ->count(),
            'hold' => JobList::when(!empty($jobIds), function ($query) use ($jobIds) {
                $query->whereIn('id', $jobIds);
            })
                ->where('status', 'hold')
                ->count(),
            'total_vacancies' => JobList::when(!empty($jobIds), function ($query) use ($jobIds) {
                $query->whereIn('id', $jobIds);
            })->sum('vacancy'),
        ];
    }

    public function getProcessPipeline()
    {
        $user = auth()->user();

        $applications = Application::with(['processes.process'])
            ->when($user && $user->type === 'agent', function ($q) use ($user) {
                $ids = $user->agent?->applications()->pluck('id') ?? [];
                $q->whereIn('id', $ids);
            })
            ->when($user && $user->type === 'client', function ($q) use ($user) {
                $q->whereHas('jobList.workOrder', function ($query) use ($user) {
                    $query->where('client_id', $user->client->id);
                });
            })
            ->get();

        $processes = Process::all();

        return $processes->map(function ($process) use ($applications) {

            $count = $applications->filter(function ($app) use ($process) {

                $latestProcess = $app->processes
                    ->sortByDesc('id')
                    ->first();

                return $latestProcess
                    && $latestProcess->process_id == $process->id
                    && $latestProcess->status != 'rejected'
                    && $latestProcess->status != 'declined'
                    && !(
                        $latestProcess->process->name == 'on_boarding'
                        && $latestProcess->status == 'completed'
                    );
            })->count();

            return [
                'process' => $process->name,
                'total' => $count,
            ];
        })->values()->toArray();
    }

    public function getFlightSummary(?int $limit = null, ?array $filters = []): array
    {
        $user = auth()->user();

        $traProcessId = Process::where('name', 'tra_process')->value('id');

        if (!$traProcessId) {
            return [];
        }

        $applications = Application::with([
            'processes.process',
            'jobList.workOrder.client.user',
            'jobList.workOrder.client',
            'jobList.workOrder.client.country',
            'jobList.principal.country',
        ])
            ->when($user && $user->type === 'agent', function ($q) use ($user) {
                $ids = $user->agent?->applications()->pluck('id') ?? [];
                $q->whereIn('id', $ids);
            })
            ->when($user && $user->type === 'client', function ($q) use ($user) {
                $q->whereHas('jobList.workOrder', function ($query) use ($user) {
                    $query->where('client_id', $user->client->id);
                });
            })
            ->whereHas('processes', function ($q) use ($traProcessId) {
                $q->where('process_id', $traProcessId)
                    ->where(function ($query) {
                        $query->whereNotNull('data->flight_no_one')
                            ->where('data->flight_no_one', '!=', '')
                            ->orWhere(function ($subQuery) {
                                $subQuery->whereNotNull('data->flight_no_two')
                                    ->where('data->flight_no_two', '!=', '');
                            });
                    });
            })
            ->when($filters['client'] ?? null,
                function ($q) use ($filters) {
                $q->whereHas('jobList.workOrder', function ($query) use ($filters) {
                    $query->where('client_id', $filters['client']);
                });
            }
            )
            ->when($filters['country'] ?? null,
                function ($q) use ($filters) {
                    $q->whereHas('jobList.workOrder.client', function ($query) use ($filters) {
                        $query->where('country_id', $filters['country']);
                    });
                }
            )
           ->whereHas('currentProcess', function ($query) use ($traProcessId) {
                $onboardingId = Process::where('name', 'on_boarding')->value('id');
                $query->where(function ($q) use ($traProcessId, $onboardingId) {
                    $q->where('process_id', $traProcessId)
                    ->orWhere('process_id', $onboardingId);
                    })
                ->whereNotIn('status', ['rejected', 'declined']);
            })
            ->when(($filters['upcoming'] ?? false) ,
                function ($q) use ($traProcessId) {
                    $today = now()->toDateString();
                    $q->whereHas('processes', function ($query) use ($traProcessId, $today) {
                        $query->where('process_id', $traProcessId)
                            ->whereDate('data->flight_date', '>=', $today);
                    });
                }
            )
            ->when(filled($filters['flight_no'] ?? null),
                function ($q) use ($filters, $traProcessId) {
                    $flightNo = trim($filters['flight_no']);
                    $q->whereHas('processes', function ($query) use ($traProcessId, $flightNo) {
                        $query->where('process_id', $traProcessId)
                            ->where(function ($sub) use ($flightNo) {
                                    $sub->where('data->flight_no_one', 'LIKE', "%{$flightNo}%")
                                        ->orWhere('data->flight_no_two', 'LIKE', "%{$flightNo}%");
                            });
                    });
                }
            )
            ->when(filled($filters['departure_from'] ?? null),
            function ($q) use ($filters, $traProcessId) {
                $q->whereHas('processes', function ($query) use ($filters, $traProcessId) {
                    $query->where('process_id', $traProcessId)
                        ->whereDate(
                                'data->flight_date',
                                '>=',
                                $filters['departure_from']
                        );
                });
            }
        )->when(filled($filters['departure_to'] ?? null),
            function ($q) use ($filters, $traProcessId) {
                $q->whereHas('processes', function ($query) use ($filters, $traProcessId) {
                    $query->where('process_id', $traProcessId)
                        ->whereDate(
                                'data->flight_date',
                                '<=',
                                $filters['departure_to']
                        );
                });
            }
        )
            ->get();

        $flightGroups = [];
        $clients = [];
        $countries = [];

        foreach ($applications as $application) {
            $traProcess = $application->processes->firstWhere('process_id', $traProcessId);
            if (!$traProcess) {
                continue;
            }

            $traData = $traProcess->data ?? [];

            $flightNo = trim((string) ($traData['flight_no_one'] ?? ''));
            if ($flightNo === '') {
                $flightNo = trim((string) ($traData['flight_no_two'] ?? ''));
            }

            if ($flightNo === '') {
                continue;
            }

            $groupKey = strtolower($flightNo);

            if (!isset($flightGroups[$groupKey])) {
                $flightGroups[$groupKey] = [
                    'flight_no' => $flightNo,
                    'clients' => [],
                    'passengers' => [],
                    'passenger_ids' => [],
                    'countries' => [],
                    'flight_date' => $traData['flight_date'] ?? null,
                    'flight_time' => $traData['flight_time'] ?? null,
                    'from_city' => $this->formatFlightFromCity($traData['flight_from_city'] ?? null),
                    'landing_airport' => $traData['landing_airport'] ?? null,
                    'landing_time' => $traData['landing_time'] ?? null,
                ];
            }

            $client = $application->jobList?->workOrder?->client;
            $country = $application->jobList?->workOrder?->client?->country;

            if($client)
                {
                $clients[] = [
                    'id' => $client->id,
                    'name' => $client->user?->name,
                    ];
                }
            if($country)
                {
                $countries[] = [
                    'id' => $country->id,
                    'name' => $country->name,
                    ];
                }

            $clientName = $application->jobList?->workOrder?->client?->user?->name;
            $countryName = $application->jobList?->workOrder?->client?->country?->name;
            $candidateName = trim(($application->given_name ?? '') . ' ' . ($application->sur_name ?? ''));

            if ($clientName && !in_array($clientName, $flightGroups[$groupKey]['clients'], true)) {
                $flightGroups[$groupKey]['clients'][] = $clientName;
            }

            if (!isset($flightGroups[$groupKey]['passenger_ids'][$application->id])) {
                $flightGroups[$groupKey]['passenger_ids'][$application->id] = true;
                $flightGroups[$groupKey]['passengers'][] = [
                    'name' => $candidateName,
                    'client' => $clientName,
                    'passport_no' => $application->passport_no,
                    'application_id' => $application->application_id
                        ? substr($application->application_id, 4)
                        : null,
                    'job_name' => $application->jobList?->name,
                    'demand_letter' => $application->jobList?->workOrder?->work_order_id,
                ];
            }

            if ($countryName && !in_array($countryName, $flightGroups[$groupKey]['countries'], true)) {
                $flightGroups[$groupKey]['countries'][] = $countryName;
            }

            if (!empty($traData['landing_airport'])) {
                $flightGroups[$groupKey]['landing_airport'] = $traData['landing_airport'];
            }

            if (!empty($traData['landing_time'])) {
                $flightGroups[$groupKey]['landing_time'] = $traData['landing_time'];
            }
        }

        $sortedFlights = collect($flightGroups)
            ->map(function (array $group) {
                return [
                    'flight_no' => $group['flight_no'],
                    'clients' => $group['clients'],
                    'client' => implode(', ', $group['clients']),
                    'candidate_count' => count($group['passengers']),
                    'country' => implode(', ', $group['countries']),
                    'flight_date' => $group['flight_date'],
                    'flight_time' => $group['flight_time'],
                    'from_city' => $group['from_city'],
                    'landing_airport' => $group['landing_airport'],
                    'landing_time' => $group['landing_time'],
                    'passengers' => $group['passengers'],
                ];
            })
            ->sortBy(function (array $item) {
                return $item['flight_date'] ?? '9999-12-31';
            })
            ->values();

        $total = $sortedFlights->count();
        $flights = $limit !== null
            ? $sortedFlights->take($limit)->values()
            : $sortedFlights;

        return [
            'data' => $flights->toArray(),
            'total' => $total,
            'has_more' => $limit !== null && $total > $limit,
            'clients' => collect($clients)->unique('id')->values()->toArray(),
            'countries' => collect($countries)->unique('id')->values()->toArray(),
        ];
    }

    private function formatFlightFromCity(?string $city): ?string
    {
        return match ($city) {
            'dhaka' => 'Dhaka',
            'chattogram' => 'Chattogram',
            'sylhet' => 'Sylhet',
            default => $city,
        };
    }

    public function getFinanceSummary()
    {
        return [
            'candidate' => $this->formatFinance(
                $this->getTransactionSummary('candidate'),
                'candidate'
            ),

            'client' => $this->formatFinance(
                $this->getTransactionSummary('client'),
                'client',
                false
            ),
        ];
    }


private function getTransactionSummary(string $payer)
{
    $isCandidate = $payer === 'candidate';

    $totalColumn = $isCandidate ? 'total_amount' : 'total_amount_usd';
    $paidColumn = $isCandidate ? 'paid_amount' : 'paid_amount_usd';

    return Transaction::whereHas('application', function ($q) use ($payer) {
        JobListPayerHelper::scopeWhereApplicationResponsible($q, $payer);
    })
    ->selectRaw("
        COUNT(*) as total_transactions,

        SUM(COALESCE($totalColumn, 0)) as total_billed,
        SUM(COALESCE($paidColumn, 0)) as total_paid,

        SUM(
            CASE
                WHEN COALESCE($totalColumn, 0) > COALESCE($paidColumn, 0)
                THEN
                    COALESCE($totalColumn, 0)
                    - COALESCE($paidColumn, 0)
                    " . ($isCandidate ? " - COALESCE(discount_amount, 0)" : "") . "
                ELSE 0
            END
        ) as total_due,

        " . ($isCandidate ? "SUM(COALESCE(discount_amount, 0))" : "0") . " as total_discount
    ")
    ->first();
}

  private function formatFinance($data, $type, $hasDiscount = true)
    {
        return [
            'total_transactions' => $data->total_transactions ?? 0,
            'total_billed' => $this->money($data->total_billed ?? 0, $type),
            'total_paid' => $this->money($data->total_paid ?? 0, $type),
            'total_due' => $this->money($data->total_due ?? 0, $type),
            'total_discount' => $hasDiscount
                ? $this->money($data->total_discount ?? 0, $type)
                : $this->money(0, $type),
        ];
    }
    private function money($amount, $type)
    {
        $symbol = $this->getCurrencySymbol($type);

        return $symbol . number_format($amount);
    }
    private function getCurrencySymbol($type)
    {
        return match ($type) {
            'candidate' => '৳',
            'client' => '$',
            default => '৳',
        };
    }
    public function getPaymentMethodSummary()
    {
        return Transaction::select('payment_method', DB::raw('count(*) as total'))->groupBy('payment_method')->get();
    }

    public function getClientBillingSummary()
    {
        return [
            'invoice_generated' => ClientTransaction::where('status', 'invoice-generated')->count(),
            'invoice_sent' => ClientTransaction::where('status', 'invoice-sent')->count(),
            'paid' => ClientTransaction::where('status', 'paid')->count(),
            'cancelled' => ClientTransaction::where('status', 'cancelled')->count(),
        ];
    }

    public function getRecentApplications()
    {
        $user = auth()->user();

        $query = Application::with('jobList')->latest();

        if ($user?->type === 'agent') {
            $query->whereHas('jobList', function ($q) use ($user) {
                $q->whereIn('id', function ($sub) use ($user) {
                    $sub->select('job_list_id')->from('applications')->where('agent_id', $user->agent->id);
                });
            });
        } elseif ($user?->type === 'client') {
            $query->whereHas('jobList.workOrder', function ($q) use ($user) {
                $q->where('client_id', $user->client->id);
            });
        }

        return $query->take(5)->get();
    }

    public function getRecentTransactions()
    {
        return Transaction::latest()->take(5)->get();
    }

    public function getRecentWorkOrders()
    {
        return WorkOrder::with('client')->latest()->take(5)->get();
    }

    public function getAlerts()
    {
        $user = auth()->user();
        if ($user && $user->type === 'agent') {
            $jobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
            return [
                'job_deadlines' => JobList::whereIn('id', $jobIds)
                    ->whereDate('deadline', '<=', now()->addDays(7))
                    ->get(),
                'interviews' => JobList::whereIn('id', $jobIds)
                    ->whereDate('interview_date', '<=', now()->addDays(7))
                    ->get(),
            ];
        } elseif ($user && $user->type === 'client') {
            $jobIds = JobList::whereHas('workOrder', function ($query) use ($user) {
                $query->where('client_id', $user->client->id);
            })
                ->pluck('id')
                ->toArray();

            return [
                'job_deadlines' => JobList::whereIn('id', $jobIds)
                    ->whereDate('deadline', '<=', now()->addDays(7))
                    ->get(),
                'interviews' => JobList::whereIn('id', $jobIds)
                    ->whereDate('interview_date', '<=', now()->addDays(7))
                    ->get(),
                'work_order_expiry' => WorkOrder::where('client_id', $user->client->id)
                    ->whereDate('end_date', '<=', now()->addDays(7))
                    ->get(),
            ];
        } else {
            return [
                'job_deadlines' => JobList::whereDate('deadline', '<=', now()->addDays(7))->get(),
                'interviews' => JobList::whereDate('interview_date', '<=', now()->addDays(7))->get(),
                'work_order_expiry' => WorkOrder::whereDate('end_date', '<=', now()->addDays(7))->get(),
            ];
        }
    }
}
