<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    /**
     * Default rule for file uploads
     * Allowed: Images or PDF
     * Max size: 400KB
     */
    private array $fileRule = [
        'nullable',
        'file',
        'mimetypes:image/*,application/pdf',
        'max:400', // max 400kb
    ];

    private array $forPdf = [
        'nullable',
        'file',
        'mimetypes:application/pdf',
        'max:400', // max 400kb
    ];

    private const APPLIED_THROUGH = ['direct_candidate', 'agent'];

    private const PAYMENT_RESPONSIBILITY = ['candidate', 'agent', 'client'];

    /**
     * Authorize request
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $paymentResponsibility = $this->input('payment_responsibility');

        if (is_string($paymentResponsibility)) {
            $decoded = json_decode($paymentResponsibility, true);
            if (is_array($decoded)) {
                $this->merge(['payment_responsibility' => $decoded]);
            } elseif ($paymentResponsibility !== '') {
                $this->merge(['payment_responsibility' => [$paymentResponsibility]]);
            }
        }

        if ($this->input('applied_through') === 'direct_candidate') {
            $payers = collect($this->input('payment_responsibility', []))
                ->map(static fn ($value) => strtolower(trim((string) $value)))
                ->reject(static fn ($value) => $value === '' || $value === 'agent')
                ->unique()
                ->values()
                ->all();

            $this->merge([
                'agent_id' => null,
                'payment_responsibility' => $payers,
            ]);
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        $id = $this->route('application'); // Used for unique validation during update
        $appliedThrough = $this->input('applied_through');

        return [
            /*
            |--------------------------------------------------------------------------
            | Basic Personal Information
            |--------------------------------------------------------------------------
            */
            'given_name' => ['required', 'string'],
            'sur_name' => ['required', 'string'],
            'marital_status' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'place_of_birth' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string'],
            'height' => ['nullable', 'string'],
            'weight' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */
            'mobile' => ['nullable', 'string'],
            'whatsapp_no' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'father_name' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],

            'experiences' => ['nullable', 'array'],
            'experiences.*.company_name' => ['nullable', 'string'],
            'experiences.*.position' => ['nullable', 'string'],
            'experiences.*.from_date' => ['nullable', 'date'],
            'experiences.*.to_date' => ['nullable', 'date', 'after_or_equal:experiences.*.from_date'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.responsibilities' => ['nullable', 'array'],
            'experiences.*.types' => ['nullable', Rule::in(['bd_exp', 'overseas_exp'])],

            /*
            |--------------------------------------------------------------------------
            | Professional Information
            |--------------------------------------------------------------------------
            */
            'qualification_id' => ['nullable', 'exists:qualifications,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'bd_exp' => ['nullable', 'string'],
            'overseas_exp' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Application Details
            |--------------------------------------------------------------------------
            */

            'job_list_id' => ['required', 'exists:job_lists,id'],
            'applied_through' => ['required', 'string', Rule::in(self::APPLIED_THROUGH)],
            'agent_id' => [
                Rule::requiredIf($appliedThrough === 'agent'),
                'nullable',
                'exists:agents,id',
            ],
            'agent_name' => ['nullable', 'string'],
            'payment_responsibility' => ['required', 'array', 'min:1'],
            'payment_responsibility.*' => ['required', 'string', Rule::in(self::PAYMENT_RESPONSIBILITY)],
            'remarks' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | NID Information
            |--------------------------------------------------------------------------
            */
            'nid_no' => ['nullable', 'string'],
            'nid_link' => ['nullable', 'string'],
            'nid_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Passport Information
            |--------------------------------------------------------------------------
            */
            'passport_no' => ['nullable', 'string', Rule::unique('applications', 'passport_no')->ignore($id)],
            'date_of_issue' => ['nullable', 'date'],
            'date_of_expiry' => ['nullable', 'date', 'after:date_of_issue'],
            'passport_link' => ['nullable', 'string'],
            'passport_path' => [
                'nullable',
                'file',
                'mimetypes:image/*,application/pdf',
                'max:1024', // max 1MB
            ],
            'passport_pdf_path' => $this->forPdf,
            'single_document_path' => [
                'nullable',
                'file',
                'mimetypes:application/pdf',
                'max:1024', // max 1MB
            ],
            /*
            |--------------------------------------------------------------------------
            | Resume / CV
            |--------------------------------------------------------------------------
            */
            'resume_link' => ['nullable', 'string'],
            'resume_path' => $this->forPdf,

            /*
            |--------------------------------------------------------------------------
            | Offer Letter
            |--------------------------------------------------------------------------
            */
            'offer_letter_link' => ['nullable', 'string'],
            'offer_letter_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Driving License
            |--------------------------------------------------------------------------
            */
            'driving_license_no' => ['nullable', 'string'],
            'driving_license_path' => $this->forPdf,

            'education_path' => $this->forPdf,
            'training_path' => $this->forPdf,
            'experience_path' => $this->forPdf,
            /*
            |--------------------------------------------------------------------------
            | Visa Copy
            |--------------------------------------------------------------------------
            */
            'visa_copy_link' => ['nullable', 'string'],
            'visa_copy_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Immigration Clearance
            |--------------------------------------------------------------------------
            */
            'immigration_clearance_link' => ['nullable', 'string'],
            'immigration_clearance_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Additional Documents
            |--------------------------------------------------------------------------
            */
            // 'documents_link' => ['nullable', 'string'],
            // 'documents_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Acknowledgment
            |--------------------------------------------------------------------------
            */
            'acknowledgment_link' => ['nullable', 'string'],
            'acknowledgment_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Worker Image
            |--------------------------------------------------------------------------
            */
            'worker_image_link' => ['nullable', 'string'],
            'worker_image_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | QVP (Quarantine Vaccination Passport)
            |--------------------------------------------------------------------------
            */
            'qvp_link' => ['nullable', 'string'],
            'qvp_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | SVP (Special Vaccination Passport)
            |--------------------------------------------------------------------------
            */
            'svp_link' => ['nullable', 'string'],
            'svp_path' => $this->fileRule,

            /*
            |--------------------------------------------------------------------------
            | Ticket Information
            |--------------------------------------------------------------------------
            */
            'ticket_link' => ['nullable', 'string'],
            'ticket_path' => $this->fileRule,
            /*
            |--------------------------------------------------------------------------
            | Discount
            |--------------------------------------------------------------------------
            */
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'create_party_account' => ['nullable', Rule::in([0, 1, '0', '1', true, false])],
        ];
    }
}
