<?php

namespace App\Modules\Journals\Resources;

use App\Modules\JobList\Models\JobList;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    public function toArray($request): array
    {
        $statusLabels = [
            'draft' => 'Draft',
            'pending_approval' => 'Waiting for Approval',
            'approved' => 'Approved',
            'posted' => 'Posted',
        ];

        return [
            'id' => $this->id,
            'voucher_no' => $this->voucher_no,
            'voucher_date' => optional($this->voucher_date)?->format('Y-m-d'),
            'voucher_date_label' => DateTimeFormatter::formatDate($this->voucher_date),
            'transaction_type' => $this->transaction_type,
            'transaction_type_name' => $this->transactionType?->name ?? $this->transaction_type,
            'reference_no' => $this->reference_no,
            'party_type' => $this->party_type,
            'party_id' => $this->party_id,
            'party_code' => $this->party?->code,
            'party_name' => $this->party?->name,
            'project_id' => $this->project_id,
            'project_name' => $this->resolveProjectName(),
            'narration' => $this->narration,
            'manager_comment' => $this->manager_comment,
            'total_debit' => number_format((float) $this->total_debit, 2, '.', ''),
            'total_credit' => number_format((float) $this->total_credit, 2, '.', ''),
            'status' => $statusLabels[$this->status] ?? ucfirst((string) $this->status),
            'status_raw' => $this->status,
            'lines' => JournalLineResource::collection($this->whenLoaded('lines')),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }

    private function resolveProjectName(): ?string
    {
        $projectId = (string) ($this->project_id ?? '');

        if ($projectId === '') {
            return null;
        }

        if (str_starts_with($projectId, 'job:')) {
            $id = (int) substr($projectId, 4);

            return $id > 0 ? JobList::query()->whereKey($id)->value('name') : null;
        }

        if (str_starts_with($projectId, 'dl:')) {
            $id = (int) substr($projectId, 3);

            return $id > 0 ? WorkOrder::query()->whereKey($id)->value('work_order_id') : null;
        }

        if (ctype_digit($projectId)) {
            return JobList::query()->whereKey((int) $projectId)->value('name');
        }

        return null;
    }
}
