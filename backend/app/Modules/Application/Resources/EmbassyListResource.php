<?php

namespace App\Modules\Application\Resources;

use App\Modules\Setting\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class EmbassyListResource extends JsonResource
{
    public function toArray($request): array
    {
          $settingData = Setting::first();
        return [
            'id' => $this->id,
            'submit_date' => DateTimeFormatter::formatDate($this->submit_date),
            'submit_date_raw' => $this->submit_date?->format('Y-m-d'),
            'submit_date_formatted' => $this->submit_date?->format('d M, y'),
            'new_stamping' => (int) ($this->new_stamping_count ?? $this->items?->where('list_type', 'new_stamping')->count() ?? 0),
            'cancel_stamping' => (int) ($this->cancel_stamping_count ?? $this->items?->where('list_type', 'cancellation')->count() ?? 0),
            'restamping' => (int) ($this->restamping_count ?? $this->items?->where('list_type', 'restamping')->count() ?? 0),
            'embassy_company_name' => $settingData?->embassy_company_name,

            'company_rl' => $settingData?->company_rl,
            'items' => EmbassyListItemResource::collection($this->whenLoaded('items')),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'last_update' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
