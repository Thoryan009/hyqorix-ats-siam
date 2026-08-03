<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobListAtsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_process_id' => [
                'required',
                'integer',
                'exists:application_processes,id',
            ],


            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'draft', 'completed', 'rejected', 'declined']),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'data' => [
                'nullable',
                'array',
            ],
            // Process:1 Offer Extended
            'data.offer_status' => [
                'nullable',
                'string',
                Rule::in(['accepted', 'rejected']),
            ],

            // Process:2 Visa Authorization
            'data.sponsor_id' => [
                'nullable',
                'integer',

            ],
            'data.visa_no' => [
                'nullable',
                'integer',

            ],
            'data.date_of_issue' => [
                'nullable',
                'date',
            ],
            'data.visa_profession' => [
                'nullable',
                'string',
                'max:255',
            ],


            // Process:3 Medical Test

            'data.date_of_medical' => [
                'nullable',
                'date',
            ],

            'data.medical_center_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'data.medical_fit' => [
                'nullable',
                'string',
                Rule::in(['fit', 'unfit', 'meet', 'under_review', 'medical_awaiting']),
            ],

            'data.unfit_reason' => [
                'nullable',
                'string',
                'max:500',
            ],
            // Process:4 Police Clearance
            'data.date_of_apply' => [
                'nullable',
                'date',

            ],

            'data.ps_name' => [
                'nullable',
                'string',
                'max:255',

            ],

            'data.not_issued_reason' => [
                'nullable',
                'string',
                'max:500',
            ],

            //Process:5 Trade Test & Process 6:  Biometric Enrollment -> Status

            'data.trade_test_date' => [
                'nullable',
                'date',

            ],

            'data.name_of_the_center' => [
                'nullable',
                'string',
                'max:255',

            ],

            'data.status' => [
                'nullable',
                'string',
                Rule::in(['pass', 'fail', 'appointment', 'approved', 'rejected', 'not_required']),
            ],

            //Process:6 Biometric Enrollment & Process:9 BMET Biometric Enrollment
            'data.mofa_status' => [
                'nullable',
                'string',
                Rule::in(['completed', 'not_completed']),
            ],

            'data.date_of_enrollment' => [
                'nullable',
                'date',
            ],

            //Process:7 Embassy Submission & Process 10: Immigration Clearances
            'data.date_of_submission' => [
                'nullable',
                'date',

            ],

            'data.endorsment_date' => [
                'nullable',
                'date',

            ],

            'data.collection_date' => [
                'nullable',
                'date',

            ],
            'data.visa_expiry' => [
                'nullable',
                'date',

            ],

            //Process:8 Bmet Training
            'data.training_start_date' => [
                'nullable',
                'date',

            ],

            'data.finished_date' => [
                'nullable',
                'date',
            ],

            // Process:9 BMET Biometric Enrollment Handle in Process 6

            //Process 10: Immigration Clearances Handle in Process 7
             'data.clearance_status' => [
                'nullable',
                'string',
                Rule::in(['awaiting', 'completed', 'under_process']),
            ],

            // Process 11: PTA Request

            'data.pta_request_date' => [
                'nullable',
                'date',
            ],

            // Process 12: TRA Process
            'data.flight_from_city' => [
                'nullable',
                'string',
                Rule::in(['dhaka', 'chattogram', 'sylhet']),
            ],

            'data.airport_of_origin' => [
                'nullable',
                'string',
            ],

            'data.airline' => [
                'nullable',
                'string',
            ],

            'data.ticket_no' => [
                'nullable',
                'string',
            ],

            'data.flight_no_one' => [
                'nullable',
                'string',
            ],

            'data.flight_no_two' => [
                'nullable',
                'string',
            ],

            'data.flight_date' => [
                'nullable',
                'date',
            ],
            'data.flight_time' => [
                'nullable',
                'string',
            ],

            'data.ticket_passport' => [
                'nullable',
                'integer',
                'in:0,1',
            ],
            // Process 13: OnBoardings
            'data.flight_status' => [
                'nullable',
                Rule::in(['missed', 'departed', 'cancelled']),
            ],
            'data.landing_airport' => [
                'nullable',
                'string',
                'max:255',
            ],
            'data.landing_date' => [
                'nullable',
                'date',
            ],
            'data.landing_time' => [
                'nullable',
                'string',
            ],
        ];
    }
}
