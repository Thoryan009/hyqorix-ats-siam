<?php

namespace App\Modules\Application\Seeders;

use App\Modules\Application\Models\Application;
use Illuminate\Database\Seeder;
use App\Modules\Application\Models\ApplicationProcess;
use App\Modules\Application\Models\Process;
use Carbon\Carbon;

class ApplicationProcessSeeder extends Seeder
{
    public function run(): void
    {
        // Current timestamp reference
        $now = Carbon::now();

        // Existing application ID (must exist in applications table)
        $applicationIds = Application::pluck('id')->values();

        if ($applicationIds->count() < 5) {
            $this->command->warn('Not enough applications found.');
            return;
        }
        $processIds = Process::pluck('id')->toArray();

        $applicationProcesses = [

            // 🔹 Application 1 → 13 processes
            [
                'application_id' => $applicationIds[0],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [
                            'offer_status' => 'accepted',
                        ],
                        'remarks' => 'Offer accepted by candidate',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(30),
                        'completed_at' => $now->copy()->subDays(28),
                    ],
                    [
                        'process_id' => 2,
                        'status' => 'completed',
                        'data' => [
                            'visa_no' => 123456,
                            'sponsor_id' => 45,
                            'date_of_issue' => '2025-01-10',
                            'visa_profession' => 'Electrician',
                        ],
                        'remarks' => 'Visa issued successfully',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(25),
                        'completed_at' => $now->copy()->subDays(20),
                    ],

                    [
                        'process_id' => 3,
                        'status' => 'completed',
                        'data' => [
                            'date_of_medical' => '2025-01-12',
                            'medical_center_name' => 'Al Hayat Medical Center',
                            'medical_fit' => 'fit',
                            'unfit_reason' => 'No issues found',
                        ],
                        'remarks' => 'Medically fit',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(23),
                        'completed_at' => $now->copy()->subDays(22),
                    ],

                    [
                        'process_id' => 4,
                        'status' => 'completed',
                        'data' => [
                            'date_of_apply' => '2025-01-14',
                            'ps_name' => 'Dhaka Metropolitan Police',
                            'not_issued_reason' => null,
                        ],
                        'remarks' => 'PCC issued',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(21),
                        'completed_at' => $now->copy()->subDays(18),
                    ],

                    [
                        'process_id' => 5,
                        'status' => 'completed',
                        'data' => [
                            'trade_test_date' => '2025-01-18',
                            'name_of_the_center' => 'Skills Testing Center',
                            'status' => 'pass',
                        ],
                        'remarks' => 'Trade test passed',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(17),
                        'completed_at' => $now->copy()->subDays(16),
                    ],


                    [
                        'process_id' => 6,
                        'status' => 'completed',
                        'data' => [
                            'mofa_status' => 'completed',
                            'date_of_enrollment' => '2025-01-20',
                            'status' => 'approved',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(15),
                        'completed_at' => $now->copy()->subDays(14),
                    ],

                    [
                        'process_id' => 7,
                        'status' => 'completed',
                        'data' => [
                            'date_of_submission' => '2025-01-22',
                            'endorsment_date' => '2025-01-25',
                            'visa_expiry' => '2025-04-25',
                            'collection_date' => '2025-01-28',
                        ],
                        'remarks' => 'Passport collected',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(13),
                        'completed_at' => $now->copy()->subDays(10),
                    ],

                    [
                        'process_id' => 8,
                        'status' => 'completed',
                        'data' => [
                            'training_start_date' => '2025-02-01',
                            'finished_date' => '2025-02-10',
                        ],
                        'remarks' => 'Training completed',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(9),
                        'completed_at' => $now->copy()->subDays(1),
                    ],

                    [
                        'process_id' => 9,
                        'status' => 'completed',
                        'data' => [
                            'date_of_enrollment' => '2025-02-12',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(5),
                        'completed_at' => $now->copy()->subDays(4),
                    ],


                    [
                        'process_id' => 10,
                        'status' => 'completed',
                        'data' => [
                            'date_of_submission' => '2025-02-13',
                            'collection_date' => '2025-02-15',
                            'clearance_status' => 'completed',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(4),
                        'completed_at' => $now->copy()->subDays(2),
                    ],

                    [
                        'process_id' => 11,
                        'status' => 'completed',
                        'data' => [
                            'pta_request_date' => '2025-02-16',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(2),
                        'completed_at' => $now->copy()->subDays(1),
                    ],

                    [
                        'process_id' => 12,
                        'status' => 'completed',
                        'data' => [
                            'flight_from_city' => 'dhaka',
                            'airport_of_origin' => 'DAC',
                            'airline' => 'Qatar Airways',
                            'ticket_no' => '12345678901112',
                            'flight_no_one' => 'QR641',
                            'flight_no_two' => 'QR642',
                            'flight_date' => '2025-02-18',
                            'flight_time' => '18:30:00',
                            'ticket_passport_handover' => 1,
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(1),
                        'completed_at' => $now,
                    ],
                    [
                        'process_id' => 13,
                        'status' => 'completed',
                        'data' => [
                            'flight_status' => 'Departed',
                            'landing_airport' => 'DOH',
                            'landing_date' => '2025-02-18',
                            'landing_time' => '22:30:00',
                        ],
                        'remarks' => 'Candidate departed successfully',
                        'created_by' => 1,
                        'started_at' => $now,
                        'completed_at' => $now,
                    ],
                ]
            ],
            // 🔹 Application 2 → 5 processes
            [
                'application_id' => $applicationIds[1],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [
                            'offer_status' => 'accepted',
                        ],
                        'remarks' => 'Offer accepted by candidate',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(20),
                        'completed_at' => $now->copy()->subDays(19),
                    ],
                    [
                        'process_id' => 3,
                        'status' => 'completed',
                        'data' => [
                            'date_of_medical' => '2025-01-15',
                            'medical_center_name' => 'Ibn Sina Diagnostic',
                            'medical_fit' => 'fit',
                            'unfit_reason' => null,
                        ],
                        'remarks' => 'Medically fit',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(18),
                        'completed_at' => $now->copy()->subDays(17),
                    ],
                    [
                        'process_id' => 4,
                        'status' => 'completed',
                        'data' => [
                            'date_of_apply' => '2025-01-18',
                            'ps_name' => 'Chattogram Metropolitan Police',
                            'not_issued_reason' => null,
                        ],
                        'remarks' => 'PCC issued',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(16),
                        'completed_at' => $now->copy()->subDays(14),
                    ],
                    [
                        'process_id' => 7,
                        'status' => 'completed',
                        'data' => [
                            'date_of_submission' => '2025-01-22',
                            'endorsment_date' => '2025-01-25',
                            'visa_expiry' => '2025-04-25',
                            'collection_date' => '2025-01-27',
                        ],
                        'remarks' => 'Passport collected',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(13),
                        'completed_at' => $now->copy()->subDays(11),
                    ],
                    [
                        'process_id' => 10,
                        'status' => 'pending',
                        'data' => [
                            'date_of_submission' => '2025-02-13',
                            'collection_date' => '2025-02-15',
                            'clearance_status' => 'under_process',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(4),
                        'completed_at' => $now->copy()->subDays(2),
                    ],
                    [
                        'process_id' => 13,
                        'status' => 'completed',
                        'data' => [
                            'flight_status' => 'Departed',
                            'landing_airport' => 'DXB',
                            'landing_date' => '2025-01-30',
                            'landing_time' => '20:00:00',
                        ],
                        'remarks' => 'Candidate departed',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(10),
                        'completed_at' => $now->copy()->subDays(10),
                    ],
                ],
            ],

            // 🔹 Application 3 → 3 processes
            [
                'application_id' => $applicationIds[2],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [
                            'offer_status' => 'accepted',
                        ],
                        'remarks' => 'Offer accepted',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(15),
                        'completed_at' => $now->copy()->subDays(14),
                    ],
                    [
                        'process_id' => 3,
                        'status' => 'completed',
                        'data' => [
                            'date_of_medical' => '2025-01-20',
                            'medical_center_name' => 'Popular Diagnostic Center',
                            'medical_fit' => 'fit',
                            'unfit_reason' => null,
                        ],
                        'remarks' => 'Fit for travel',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(13),
                        'completed_at' => $now->copy()->subDays(12),
                    ],
                    [
                        'process_id' => 13,
                        'status' => 'completed',
                        'data' => [
                            'flight_status' => 'Scheduled',
                            'landing_airport' => 'DXB',
                            'landing_date' => '2025-01-30',
                            'landing_time' => '20:00:00',
                        ],
                        'remarks' => 'Waiting for flight',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(11),
                        'completed_at' => $now->copy()->subDays(11),
                    ],
                ],
            ],


            // 🔹 Application 4 → 2 processes
            [
                'application_id' => $applicationIds[3],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [
                            'offer_status' => 'accepted',
                        ],
                        'remarks' => 'Candidate accepted offer',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(10),
                        'completed_at' => $now->copy()->subDays(9),
                    ],
                    [
                        'process_id' => 3,
                        'status' => 'completed',
                        'data' => [
                            'date_of_medical' => '2025-01-25',
                            'medical_center_name' => 'Labaid Diagnostic',
                            'medical_fit' => 'fit',
                            'unfit_reason' => null,
                        ],
                        'remarks' => 'Medically cleared',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(8),
                        'completed_at' => $now->copy()->subDays(7),
                    ],
                ],
            ],


            // 🔹 Application 5 → 1 process
            [
                'application_id' => $applicationIds[4],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [
                            'offer_status' => 'pending',
                        ],
                        'remarks' => 'Waiting for candidate response',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(5),
                        'completed_at' => null,
                    ],
                ],
            ],

            [
                'application_id' => $applicationIds[5],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => ['offer_status' => 'accepted'],
                        'remarks' => 'Offer accepted',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(15),
                        'completed_at' => $now->copy()->subDays(14),
                    ],
                    // 🔥 CURRENT PROCESS (ACTIVE)
                    [
                        'process_id' => 3,
                        'status' => 'pending',
                        'data' => ['note' => 'Medical ongoing'],
                        'remarks' => 'Medical in progress',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(3), // <50%
                        'completed_at' => null,
                    ],
                ],
            ],

            [
                'application_id' => $applicationIds[6],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => ['offer_status' => 'accepted'],
                        'remarks' => 'Offer accepted',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(25),
                        'completed_at' => $now->copy()->subDays(24),
                    ],
                    // 🔥 CURRENT PROCESS (EXPIRING)
                    [
                        'process_id' => 2,
                        'status' => 'pending',
                        'data' => [
                            'visa_no' => 123456,
                            'sponsor_id' => 45,
                            'date_of_issue' => '2025-01-10',
                            'visa_profession' => 'Electrician',
                        ],
                        'remarks' => 'Visa pending',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(18), // 60%
                        'completed_at' => null,
                    ],
                ],
            ],
            [
                'application_id' => $applicationIds[7],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => ['offer_status' => 'accepted'],
                        'remarks' => 'Offer accepted',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(45),
                        'completed_at' => $now->copy()->subDays(44),
                    ],
                    // 🔥 CURRENT PROCESS (EXPIRED)
                    [
                        'process_id' => 3,
                        'status' => 'pending',
                        'data' => [
                            'date_of_medical' => '2025-01-01',
                            'medical_center_name' => 'Expired Medical Center',
                            'medical_fit' => 'fit',
                            'unfit_reason' => null,
                        ],
                        'remarks' => 'Medical overdue',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(40), // >100%
                        'completed_at' => null,
                    ],
                ],
            ],

            [
                'application_id' => $applicationIds[8],
                'processes' => [
                    [
                        'process_id' => 1,
                        'status' => 'pending',
                        'data' => [
                            'training_start_date' => '2025-01-18',
                            'finished_date' => null,
                        ],
                        'remarks' => 'Waiting for response',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(2),
                        'completed_at' => null,
                    ],
                ],
            ],
            // newly seeded
            [
                'application_id' => Application::where('application_id', 'APP-010')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => ['offer' => 'accepted'],
                        'remarks' => 'Offer accepted',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(3), // duration 2 → progress >100%
                        'completed_at' => $now->copy()->subDays(1),
                    ],
                    // Expiring
                    [
                        'process_id' => 3,
                        'status' => 'pending',
                        'data' => [
                            'date_of_medical' => '2025-01-18',
                            'medical_center_name' => 'Dhaka Medical Center',
                            'medical_fit' => 'fit',
                            'unfit_reason' => 'No issues found',
                        ],
                        'remarks' => 'Medical ongoing',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(2), // duration 4 → 50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 5,
                        'status' => 'pending',
                        'data' => ['trade_test_date' => '2025-01-20', 'name_of_the_center' => 'Trade Test Center', 'status' => 'pass'],
                        'remarks' => 'Trade pending',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 7 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],


            /* ================= APP-011 ================= */
            [
                'application_id' => Application::where('application_id', 'APP-011')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 2,
                        'status' => 'completed',
                        'data' => [
                            'visa_no' => 123456,
                            'sponsor_id' => 45,
                            'date_of_issue' => '2025-01-10',
                            'date_of_expiry' => '2025-04-10',
                            'visa_profession' => 'Electrician',
                        ],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(4), // duration 2 → >100%
                        'completed_at' => $now->copy()->subDays(2),
                    ],
                    // Expiring
                    [
                        'process_id' => 4,
                        'status' => 'pending',
                        'data' => ['date_of_apply' => '2025-01-15', 'ps_name' => 'Dhaka Metropolitan Police', 'not_issued_reason' => null],
                        'remarks' => 'Police clearance expiring',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(7), // duration 14 → 50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 6,
                        'status' => 'pending',
                        'data' => ['mofa_status' => 'completed', 'date_of_enrollment' => '2025-01-20', 'status' => 'approved'],
                        'remarks' => 'Biometric ongoing',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 2 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],


            /* ================= APP-012 ================= */
            [
                'application_id' => Application::where('application_id', 'APP-012')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 5,
                        'status' => 'completed',
                        'data' => ['trade_test_date' => '2025-01-20', 'name_of_the_center' => 'Trade Test Center', 'status' => 'pass'],
                        'remarks' => 'Trade done',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(8), // duration 7 → >100%
                        'completed_at' => $now->copy()->subDays(6),
                    ],
                    // Expiring
                    [
                        'process_id' => 6,
                        'status' => 'pending',
                        'data' => [
                            'mofa_status' => 'completed',
                            'date_of_enrollment' => '2025-01-20',
                            'status' => 'approved',
                        ],
                        'remarks' => 'Biometric expiring',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(1), // duration 2 → ~50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 8,
                        'status' => 'pending',
                        'data' => ['training_start_date' => '2025-01-18', 'finished_date' => null],
                        'remarks' => 'Training running',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 3 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],


            /* ================= APP-013 ================= */
            [
                'application_id' => Application::where('application_id', 'APP-013')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 1,
                        'status' => 'completed',
                        'data' => [],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(3), // duration 2 → >100%
                        'completed_at' => $now->copy()->subDays(1),
                    ],
                    // Expiring
                    [
                        'process_id' => 3,
                        'status' => 'pending',
                        'data' => [],
                        'remarks' => 'Medical expiring',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(2), // duration 4 → 50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 7,
                        'status' => 'pending',
                        'data' => ['date_of_submission' => '2025-01-22', 'endorsment_date' => '2025-01-25',  'visa_expiry' => '2025-04-25','collection_date' => '2025-01-28'],
                        'remarks' => 'Ticket pending',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 2 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],


            /* ================= APP-014 ================= */
            [
                'application_id' => Application::where('application_id', 'APP-014')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 4,
                        'status' => 'completed',
                        'data' => [],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(8), // duration 7 → >100%
                        'completed_at' => $now->copy()->subDays(6),
                    ],
                    // Expiring
                    [
                        'process_id' => 5,
                        'status' => 'pending',
                        'data' => [],
                        'remarks' => 'Biometric near deadline',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(1), // duration 2 → 50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 6,
                        'status' => 'pending',
                        'data' => [],
                        'remarks' => 'PCC delayed',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 14 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],


            /* ================= APP-015 ================= */
            [
                'application_id' => Application::where('application_id', 'APP-015')->first()->id,
                'processes' => [
                    // Expired
                    [
                        'process_id' => 2,
                        'status' => 'completed',
                        'data' => [],
                        'remarks' => null,
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(3), // duration 2 → >100%
                        'completed_at' => $now->copy()->subDays(1),
                    ],
                    // Expiring
                    [
                        'process_id' => 8,
                        'status' => 'pending',
                        'data' => [],
                        'remarks' => 'Training ongoing',
                        'created_by' => 1,
                        'started_at' => $now->copy()->subDays(1), // duration 3 → 50%
                        'completed_at' => null,
                    ],
                    // Active
                    [
                        'process_id' => 7,
                        'status' => 'pending',
                        'data' => [],
                        'remarks' => 'Ticket booking',
                        'created_by' => 1,
                        'started_at' => $now->copy(), // duration 2 → 0%
                        'completed_at' => null,
                    ],
                ],
            ],
        ];

        // Insert into application_processes table
        foreach ($applicationProcesses as $group) {
            foreach ($group['processes'] as $process) {

                ApplicationProcess::create([
                    'application_id' => $group['application_id'],
                    'process_id' => $process['process_id'],
                    'status' => $process['status'],
                    'data' => $process['data'] ?? null,
                    'remarks' => $process['remarks'] ?? null,
                    'created_by' => $process['created_by'] ?? null,
                    'started_at' => $process['started_at'] ?? null,
                    'completed_at' => $process['completed_at'] ?? null,
                ]);
            }
        }

        $this->command->info('Application processes seeded successfully.');
    }
}
