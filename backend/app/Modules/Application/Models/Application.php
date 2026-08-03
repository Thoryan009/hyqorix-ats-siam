<?php

namespace App\Modules\Application\Models;

use App\Modules\Auth\Models\User;
use App\Traits\TracksUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Application extends Model
{
    use TracksUser;
    use LogsActivity;

    protected $guarded = [];
    protected $appends = ['passport_url', 'single_document_url', 'nid_url', 'resume_url', 'current_process_name', 'documents_url', 'offer_letter_url', 'acknowledgment_url', 'worker_image_url', 'immigration_clearance_url', 'visa_copy_url', 'driving_license_url', 'age', 'svp_url', 'qvp_url', 'ticket_url', 'application_status', 'education_url', 'training_url', 'experience_url', 'driving_license_url', 'passport_pdf_url'];
    protected $casts = [
        'application_status_start_date' => 'datetime',
        'payment_responsibility' => 'array',
    ];

    public function embassySubmission()
    {
        return $this->hasOne(EmbasySubmission::class);
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }

        $age = Carbon::parse($this->date_of_birth)->age;
        return $age;
    }

    public function currentProcessDays()
    {
        $applicationStatuses = ['hiring_list', 'waiting_list', 'rejected_list', 'short_list'];
        $toDate = Carbon::now();

        if(!$this->currentProcess && in_array($this->application_status, $applicationStatuses)) {
                return $this->application_status_start_date ? floor($this->application_status_start_date->diffInDays($toDate)) : null;
        }

        $startDate = $this->currentProcess?->started_at;

        if ($this->currentProcess?->process?->name === 'on_boarding' && $this->currentProcess->status === 'completed') {
            $startDate = $this->currentProcess->completed_at;
        }

        return (floor($startDate?->diffInDays($toDate)) + 1);
    }

    public function totalProcessDays()
    {
        $toDate = $this->currentProcess?->process?->name === 'on_boarding' && $this->currentProcess->status === 'completed' ? $this->currentProcess->completed_at : Carbon::now();
        return (floor($this->created_at?->diffInDays($toDate)) + 1);
    }

    public function getEducationUrlAttribute(): ?string
    {
        return $this->education_path ? asset('storage/' . $this->education_path) : null;
    }

    public function getPassportPdfUrlAttribute(): ?string
    {
        return $this->passport_pdf_path ? asset('storage/' . $this->passport_pdf_path) : null;
    }

    public function getTrainingUrlAttribute(): ?string
    {
        return $this->training_path ? asset('storage/' . $this->training_path) : null;
    }

    public function getExperienceUrlAttribute(): ?string
    {
        return $this->experience_path ? asset('storage/' . $this->experience_path) : null;
    }

    public function getDrivingLicenseUrlAttribute(): ?string
    {
        return $this->driving_license_path ? asset('storage/' . $this->driving_license_path) : null;
    }

    public function getPassportUrlAttribute(): ?string
    {
        return $this->passport_path ? asset('storage/' . $this->passport_path) : null;
    }

    public function getSvpUrlAttribute(): ?string
    {
        return $this->svp_path ? asset('storage/' . $this->svp_path) : null;
    }

    public function getQvpUrlAttribute(): ?string
    {
        return $this->qvp_path ? asset('storage/' . $this->qvp_path) : null;
    }

    public function getNidUrlAttribute(): ?string
    {
        return $this->nid_path ? asset('storage/' . $this->nid_path) : null;
    }

    public function getWorkerImageUrlAttribute(): ?string
    {
        return $this->worker_image_path ? asset('storage/' . $this->worker_image_path) : null;
    }

    public function getResumeUrlAttribute(): ?string
    {
        return $this->resume_path ? asset('storage/' . $this->resume_path) : null;
    }

    public function getDocumentsUrlAttribute(): ?string
    {
        return $this->documents_path ? asset('storage/' . $this->documents_path) : null;
    }

    public function getSingleDocumentUrlAttribute(): ?string
    {
        return $this->single_document_path ? asset('storage/' . $this->single_document_path) : null;
    }

    public function getOfferLetterUrlAttribute(): ?string
    {
        return $this->offer_letter_path ? asset('storage/' . $this->offer_letter_path) : null;
    }

    public function getImmigrationClearanceUrlAttribute(): ?string
    {
        return $this->immigration_clearance_path ? asset('storage/' . $this->immigration_clearance_path) : null;
    }

    public function getVisaCopyUrlAttribute(): ?string
    {
        return $this->visa_copy_path ? asset('storage/' . $this->visa_copy_path) : null;
    }

    public function getAcknowledgmentUrlAttribute(): ?string
    {
        return $this->acknowledgment_path ? asset('storage/' . $this->acknowledgment_path) : null;
    }

    public function getTicketUrlAttribute(): ?string
    {
        return $this->ticket_path ? asset('storage/' . $this->ticket_path) : null;
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function country()
    {
        return $this->belongsTo(\App\Modules\Country\Models\Country::class);
    }


    public function agent()
    {
        return $this->belongsTo(\App\Modules\Agent\Models\Agent::class);
    }

    public function jobList()
    {
        return $this->belongsTo(\App\Modules\JobList\Models\JobList::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function experiences()
    {
        return $this->hasMany(ApplicationExperience::class);
    }

    public function currentDue(): float
    {
        return $this->transactions->groupBy('bill_no')->sum(function ($transactions) {
            $totalAmount = $transactions->first()->total_amount;
            $totalPaid = $transactions->sum('paid_amount');

            return $totalAmount - $totalPaid;
        });
    }

    public function totalPaidAmount(): int
    {
        return (int) $this->transactions()->sum('paid_amount');
    }
    public function totalPaidAmountUsd(): int
    {
        return (int) $this->transactions()->sum('paid_amount_usd');
    }

    public function billNo(): string
    {
        return $this->transactions->pluck('bill_no')->unique()->first() ?? '';
    }
    public function processes()
    {
        return $this->hasMany(ApplicationProcess::class);
    }

    public function currentProcess()    
    {
        return $this->hasOne(ApplicationProcess::class)->latestOfMany();
    }

    public function process(string $type)
    {
        return $this->processes()->process()->where('name', $type);
    }

    public function currentTransaction()
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }
    public function getResolvedCurrentProcessAttribute()
    {
        $current = $this->currentProcess;

        if (!$current || !$current->process) {
            return null;
        }

        if ($current->process->name === 'on_boarding' && $current->status === 'completed') {
            return 'deployed';
        }

        if (in_array($current->status, ['rejected', 'declined'])) {
            return $current->status;
        }

        return $current->process->name;
    }

    public function getCurrentProcessNameAttribute()
    {
        return $this->currentProcess?->process?->name ?? 'N/A';
    }

    public function getApplicationStatusAttribute(): string
    {
        $status = $this->getAttributeFromArray('application_status');

        if (empty($status) || $status === 'ATS') {
            if ($this->currentProcess?->process?->name === 'on_boarding' && $this->currentProcess?->status === 'completed') {
                return 'deployed';
            }
            if (in_array($this->currentProcess?->status, ['rejected', 'declined'])) {
                return $this->currentProcess->status;
            }
            return $this->current_process_name ?? 'hiring_list';
        }

        return $status;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
