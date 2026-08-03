<?php

namespace App\Modules\Client\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            // Client-specific
            'client_id' => $this->client_id,

            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'client_image_url' => $this->client_image_url,
            'send_notification' => $this->send_notification,

            'type' => $this->user->type,
            'status' => $this->user->status,
            'status_formatted' => $this->user->status == 1 ? 'Active' : 'Inactive',
            'country' => $this->country->name,
            'country_id' => $this->country_id,
            'role' => count($this->user->roles) > 0 ? $this->user->roles->pluck('name')->implode(', ') : null,
            'role_id' =>  count($this->user->roles) > 0 ? $this->user->roles->first()->id : null,
            'work_orders' => $this->workOrders->map(function ($workOrder) {
                return [
                    'id' => $workOrder->id,
                    'work_order_id' => $workOrder->work_order_id,
                    'candidates' => $workOrder->candidates,
                    'end_date' => $workOrder->end_date,
                    'end_date_formatted' => DateTimeFormatter::formatDate($workOrder->end_date),
                    'price' => $workOrder->price,
                    'price_usd' => $workOrder->price_usd,
                    'client_id' => $workOrder->client_id,
                    'client' => $workOrder->client->user->name,
                    'created_at' => DateTimeFormatter::formatDateTime($workOrder->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($workOrder->updated_at),
                    'created_by' => $workOrder->createdBy ? $workOrder->createdBy->name : null,
                    'updated_by' => $workOrder->updatedBy ? $workOrder->updatedBy->name : null,
                ];
            }),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
