<?php

namespace App\Modules\PassportHandover\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class PassportHandoverResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'handover_no' => $this->handover_no,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'is_permanent' => (bool) $this->is_permanent,
            'taker_name' => $this->taker_name,
            'taker_phone' => $this->taker_phone,
            'taken_at' => DateTimeFormatter::formatDate($this->taken_at),
            'taken_at_raw' => $this->taken_at?->format('Y-m-d'),
            'expected_return_date' => DateTimeFormatter::formatDate($this->expected_return_date),
            'expected_return_date_raw' => $this->expected_return_date?->format('Y-m-d'),
            'taken_reason' => $this->taken_reason,
            'return_date' => DateTimeFormatter::formatDate($this->return_date),
            'return_date_raw' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'total_items_count' => (int) ($this->total_items_count ?? $this->items?->count() ?? 0),
            'collected_items_count' => (int) ($this->collected_items_count ?? 0),
            'rejected_items_count' => (int) ($this->rejected_items_count ?? 0),
            'pending_items_count' => (int) ($this->pending_items_count ?? 0),
            'items' => PassportHandoverItemResource::collection($this->whenLoaded('items')),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
            'handed_over_by' => $this->handed_over_by,
            'handed_over_by_name' => $this->handedOverBy?->user?->name ?? $this->createdBy?->name,
        ];
    }

    private function statusLabel(): string
    {
        if ($this->is_permanent && $this->status === 'handed_over') {
            return 'Permanent';
        }

        return match ($this->status) {
            'collected' => 'Collected',
            'partially_collected' => 'Partially Collected',
            'rejected' => 'Rejected',
            default => 'Handed Over',
        };
    }

    private function typeLabel(): string
    {
        $structure = $this->type === 'group' ? 'Group' : 'Single';

        if ($this->is_permanent) {
            return "Permanent {$structure}";
        }

        return $structure;
    }
}
