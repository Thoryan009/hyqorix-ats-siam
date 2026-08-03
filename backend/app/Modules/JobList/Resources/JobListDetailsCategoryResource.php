<?php

namespace App\Modules\JobList\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class JobListDetailsCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fees' => $this->jobListDetailsHeads->map(function ($head) {
                return [
                    'id' => $head->id,
                    'name' => $head->name,
                    'amount' => $head->amount,
                    'amount_usd' => $head->amount_usd,
                ];
            }),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
