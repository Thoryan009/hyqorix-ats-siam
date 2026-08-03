<?php

namespace App\Modules\Auth\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Application\Resources\ApplicationResource;
use App\Modules\Application\Resources\TransactionResource;
use App\Modules\WorkOrder\Resources\WorkOrderResource;


class DashboardResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'summary' => $this['summary'],

            'work_orders' => $this['work_orders'],

            'jobs' => $this['jobs'],

            'pipeline' => $this['pipeline'],

            'flight_summary' => $this['flight_summary'],

            'finance' => $this['finance'],

            'payment_methods' => $this['payment_methods'],

            'client_billing' => $this['client_billing'],

            'recent_applications' => ApplicationResource::collection($this['recent_applications']),

            'recent_transactions' => TransactionResource::collection($this['recent_transactions']),

            'recent_work_orders' => WorkOrderResource::collection($this['recent_work_orders']),

            'alerts' => $this['alerts']
        ];
    }
}
