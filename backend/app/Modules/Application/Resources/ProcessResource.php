<?php

namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class ProcessResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'duration' => (int) $this->duration,
            'validity' => $this->validity !== null ? (int) $this->validity : null,
            'notify_before' => $this->notify_before !== null ? (int) $this->notify_before : null,   

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
