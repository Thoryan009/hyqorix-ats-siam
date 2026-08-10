<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Models\Application;
use App\Modules\Application\Requests\ApplicationRequest;
use App\Modules\Application\Requests\BulkStatusUpdateRequest;
use App\Modules\Application\Resources\ApplicationResource;
use App\Modules\Application\Services\ApplicationService;
use App\Modules\JobList\Models\JobList;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Application\Services\ApplicationDataDbService;
use App\Modules\Application\Services\EmbasySubmissionService;
use App\Modules\Setting\Requests\EmbassyRequest;
use App\Traits\HandlesClientFilter;
use Illuminate\Support\Facades\Http;
use App\Services\OcrService;
use App\Services\PdfMergeService;
use App\Modules\Application\Models\Application as ApplicationModel;

class ApplicationController extends Controller
{
    use HandlesClientFilter;
    public function __construct(private readonly ApplicationService $service, private readonly ApplicationDataDbService $applicationDataDbService, private readonly OcrService $ocrService, private readonly PdfMergeService $pdfMergeService, private readonly EmbasySubmissionService $embasySubmissionService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        // Basic filters from request
        $filters = $request->filters();
        $filters['work_order_id'] = (int) $request->get('work_order_id', null);
        $filters['job_list_id'] = (int) $request->get('job_list_id', null);
        $filters['process_id'] = (int) $request->get('process_id', null);
        $filters['application_status'] = $request->get('application_status', null);
        $filters['agent_id'] = (int) $request->get('agent_id', null);
        $filters['payment_responsibility'] = $request->get('payment_responsibility', null);
        $filters['exclude_rejected_declined'] = $request->get('exclude_rejected_declined', null);

        $user = auth()->user();
        if ($user && $user->type === 'agent') {
            $agentApplicationIds = $user->agent?->applications()->pluck('id')->toArray() ?? [];
            $filters['application_ids'] = $agentApplicationIds;
        }

        // Apply reusable client filter
        $filters = $this->applyClientFilter($request, $filters);

        // Fetch data
        $data = $this->service->getPaginatedDataWithCache($filters);

        return ApplicationResource::collection($data);
    }

    public function mergeDocuments(int $applicationId)
    {
        try {

            // 1. Get application
            $application = $this->service->getById($applicationId);

            if (!$application) {
                return response()->json(['error' => 'Application not found'], 404);
            }

            if ($application->single_document_path) {
                return apiSuccess(
                    [
                        'file' => asset('storage/' . $application->single_document_path),
                    ],
                    'Single document already exists',
                );
            }

            // 2. Collect available files (optional fields)
            $files = [];
            if ($application->resume_path) {
                $files[] = $application->resume_path;
            }

            if ($application->experience_path) {
                $files[] = $application->experience_path;
            }

            if ($application->education_path) {
                $files[] = $application->education_path;
            }

            if ($application->training_path) {
                $files[] = $application->training_path;
            }

            if ($application->passport_pdf_path) {
                $files[] = $application->passport_pdf_path;
            }

            if ($application->driving_license_path) {
                $files[] = $application->driving_license_path;
            }


            // ❗ If no files
            if (empty($files)) {
                return response()->json(['error' => 'No documents found to merge'], 404);
            }

            // 3. Call service (IMPORTANT: pass paths, not UploadedFile)
            $mergedPath = $this->pdfMergeService->mergeFromStoragePaths($files);

            // 4. Update application
            $application->update([
                'documents_path' => $mergedPath,
            ]);

            return apiSuccess(
                [
                    'file' => asset('storage/' . $mergedPath),
                ],
                'Documents merged successfully',
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function generateWorkerSummary($data): ?string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (blank($apiKey)) {
            return null;
        }

        try {
            $url = "{$baseUrl}/chat/completions";

            // 🔥 Prepare clean input text (IMPORTANT for speed + quality)
            $data['job_name'] = JobList::find($data['job_list_id'])->name ?? 'N/A';
            $inputText = $this->buildSummaryInput($data);

            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->timeout(15)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional worker profile summarizer.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->summaryPrompt($inputText),
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 150,
                ]);

            if ($response->failed()) {
                return null;
            }

            $result = $response->json();
            $aiText = $result['choices'][0]['message']['content'] ?? null;

            return is_string($aiText) ? trim($aiText) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildSummaryInput($data): string
    {
        return "
            Name: {{$data['given_name']}} {{$data['sur_name']}}
            Date of Birth: " .
            ($data['date_of_birth'] ?? 'N/A') .
            "
            Nationality: " .
            ($data['nationality'] ?? 'N/A') .
            "
            Gender: " .
            ($data['sex'] ?? 'N/A') .
            "

            Mobile: " .
            ($data['mobile'] ?? 'N/A') .
            "
            Email: " .
            ($data['email'] ?? 'N/A') .
            "

            Qualification: " .
            ($data['qualification'] ?? 'N/A') .
            "
            Bangladesh Experience: " .
            ($data['bd_exp'] ?? 'N/A') .
            "
            Overseas Experience: " .
            ($data['overseas_exp'] ?? 'N/A') .
            "
            Languages: " .
            ($data['language'] ?? 'N/A') .
            "
            Applied Job: " .
            ($data['job_name'] ?? 'N/A') .
            "

            Passport No: " .
            ($data['passport_no'] ?? 'N/A') .
            "
            ";
    }

    private function summaryPrompt($text): string
    {
        return <<<PROMPT
        Create a professional worker summary in about 100 words.

        Rules:
        - Simple English
        - MUST mention the applied job role clearly
        - Highlight relevant skills and experience for that job
        - Do not add extra or fake information
        - Keep it around 30-35 words
        - No JSON, only plain text

        Data:
        $text
        PROMPT;
    }

    public function store(ApplicationRequest $request): JsonResponse
    {
        $storedFiles = []; // Keep track of stored files in case we need to rollback

        try {
            $fileFields = [
                'passport_path' => 'passports',
                'passport_pdf_path' => 'passports',
                'single_document_path' => 'single_documents',
                'nid_path' => 'nids',
                'resume_path' => 'resumes',
                'documents_path' => 'documents',
                'offer_letter_path' => 'offer_letters',
                'acknowledgment_path' => 'acknowledgments',
                'worker_image_path' => 'worker_images',
                'visa_copy_path' => 'visa_copies',
                'immigration_clearance_path' => 'immigration_clearances',
                'qvp_path' => 'qvps',
                'svp_path' => 'svps',
                'ticket_path' => 'tickets',
                'education_path' => 'education_documents',
                'training_path' => 'training_documents',
                'experience_path' => 'experience_documents',
                'driving_license_path' => 'driving_licenses',
            ];

            $data = $request->validated(); // Get validated input
            foreach ($fileFields as $field => $directory) {
                if ($request->hasFile($field)) {
                    $path = FileHelper::store($request->file($field), $directory);
                    $data[$field] = $path;
                    $storedFiles[$field] = $path;
                }
            }

            $data['summary'] = $this->generateWorkerSummary($data);

            // Extract experiences before creating the record
            $experiences = $data['experiences'] ?? [];
            unset($data['experiences']); // Remove from main data

            $record = $this->service->create($data);

            // Create experiences if provided
            if (!empty($experiences)) {
                $record->experiences()->createMany($experiences);
            }

            return apiSuccess(new ApplicationResource($record), 'created');
        } catch (\Throwable $error) {
            // Delete any files that were stored before the error
            foreach ($storedFiles as $path) {
                FileHelper::delete($path);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to create record.',
                    'error' => $error->getMessage(), // safer to return only the message
                ],
                500,
            );
        }
    }

    public function bulkUpload(Request $request, int $job_id, int $agent_id): JsonResponse
    {
        try {
            $result = $this->service->bulkUpload($request->all(), $job_id, $agent_id);

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to process bulk upload.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function show(int $id): ApplicationResource
    {
        // 🔴 Client restriction
        $user = auth()->user();
        if ($user && $user->type === 'client') {
            $clientId = $user->client?->id;

            $belongsToClient = Application::where('id', $id)
                ->whereHas('jobList.workOrder', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->exists();

            if (!$belongsToClient) {
                abort(403, 'Unauthorized application access');
            }
        }
        return new ApplicationResource($this->service->getById($id));
    }

    public function update(ApplicationRequest $request, int $id): JsonResponse
    {
        $storedFiles = []; // Track new files in case we need to rollback

        try {
            $record = $this->service->getById($id);

            $fileFields = [
                'passport_path' => 'passports',
                'passport_pdf_path' => 'passports',
                'nid_path' => 'nids',
                'resume_path' => 'resumes',
                'documents_path' => 'documents',
                'single_document_path' => 'single_documents',
                'offer_letter_path' => 'offer_letters',
                'acknowledgment_path' => 'acknowledgments',
                'worker_image_path' => 'worker_images',
                'visa_copy_path' => 'visa_copies',
                'immigration_clearance_path' => 'immigration_clearances',
                'qvp_path' => 'qvps',
                'svp_path' => 'svps',
                'ticket_path' => 'tickets',
                'education_path' => 'education_documents',
                'training_path' => 'training_documents',
                'experience_path' => 'experience_documents',
                'driving_license_path' => 'driving_licenses',
            ];

            $data = $request->validated(); // Base validated data
            foreach ($fileFields as $field => $directory) {
                if ($request->hasFile($field)) {
                    // Store new file
                    $path = FileHelper::store($request->file($field), $directory);
                    $data[$field] = $path;
                    $storedFiles[$field] = $path;

                    // Delete old file if exists
                    if (!empty($record->$field)) {
                        FileHelper::delete($record->$field);
                    }
                } else {
                    // Keep existing path if no new file uploaded
                    $data[$field] = $record->$field;
                }
            }

            // Extract experiences before updating the record
            $experiences = $data['experiences'] ?? null;
            unset($data['experiences']); // Remove from main data

            $record = $this->service->update($id, $data);

            // Update experiences if provided
            if ($experiences !== null) {
                // Delete existing experiences and create new ones
                $record->experiences()->delete();
                if (!empty($experiences)) {
                    $record->experiences()->createMany($experiences);
                }
            }

            return apiSuccess(new ApplicationResource($record), 'updated');
        } catch (\Throwable $error) {
            // Rollback any newly stored files
            foreach ($storedFiles as $path) {
                FileHelper::delete($path);
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update record.',
                    'error' => $error->getMessage(), // safer than returning full Throwable
                ],
                500,
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->service->getById($id);

        $fileFields = ['passport_path', 'passport_pdf_path', 'nid_path', 'resume_path', 'documents_path', 'offer_letter_path', 'acknowledgment_path', 'worker_image_path', 'visa_copy_path', 'immigration_clearance_path', 'qvp_path', 'svp_path', 'ticket_path', 'education_path', 'training_path', 'experience_path', 'driving_license_path', 'single_document_path'];

        // Delete all associated files
        foreach ($fileFields as $field) {
            if (!empty($record->$field)) {
                FileHelper::delete($record->$field);
            }
        }

        $this->service->delete($id);

        return apiSuccess(null, 'deleted');
    }

    // delete images when updating with new images
    public function deleteFile(Request $request, int $id): JsonResponse
    {
        $fileKey = $request->get('file_key');

        $record = $this->service->getById($id);

        if (in_array($fileKey, ['passport_path', 'passport_pdf_path', 'nid_path', 'resume_path', 'documents_path', 'offer_letter_path', 'acknowledgment_path', 'worker_image_path', 'visa_copy_path', 'immigration_clearance_path', 'qvp_path', 'svp_path', 'ticket_path', 'education_path', 'training_path', 'experience_path', 'driving_license_path', 'single_document_path'])) {
            if (!empty($record->$fileKey)) {
                FileHelper::delete($record->$fileKey);

                // Update the record to remove the file path
                $this->service->update($id, [$fileKey => null]);
                return apiSuccess($record, 'deleted', 200, 'File');
            }
        }
        return response()->json(
            [
                'success' => false,
                'message' => 'File not found or invalid file key.',
            ],
            404,
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->ids;
        $fileFields = ['passport_path', 'passport_pdf_path', 'nid_path', 'resume_path', 'documents_path', 'offer_letter_path', 'acknowledgment_path', 'worker_image_path', 'visa_copy_path', 'immigration_clearance_path', 'qvp_path', 'svp_path', 'ticket_path', 'education_path', 'training_path', 'experience_path', 'driving_license_path', 'single_document_path'];

        // Fetch all records to delete
        $records = [];
        foreach ($ids as $id) {
            $records[] = $this->service->getById($id);
        }

        // Delete associated files
        foreach ($records as $record) {
            foreach ($fileFields as $field) {
                if (!empty($record->$field)) {
                    FileHelper::delete($record->$field);
                }
            }
        }

        // Delete all records from DB
        $this->service->bulkDelete($ids);

        $this->service->bulkDelete($request->ids);
        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function bulkStatusUpdate(BulkStatusUpdateRequest $request): JsonResponse
    {
        $statuses = ['hiring_list', 'waiting_list', 'rejected_list', 'short_list']; // example statuses
        $ids = $request->validated()['ids'];
        $status = $request->validated()['status'];

        // check if any of the ids has raw_application_status as 'ATS'
        $applications = Application::whereIn('id', $ids)->get();
        if ($applications->some(function ($application) {
            return $application->getRawOriginal('application_status') === 'ATS';
        })) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'ATS applications cannot be moved to any other status',
                ],
                400,
            );
        }

        if (in_array($status, $statuses)) {
            $this->service->updateApplicaitionStatusStartDate($ids);
        }


        $this->service->bulkStatusUpdate($ids, $status);

        return apiSuccess(null, 'updated', 200, 'Application status');
    }

    public function getAllProcesses()
    {
        $processes = $this->service->getAllProcesses();

        return response()->json([
            'success' => true,
            'data' => $processes,
        ]);
    }

    public function getApplicationData()
    {
        $applicationData = $this->applicationDataDbService->getApplicationData();
        return apiSuccess($applicationData, 'fetched');
    }

    public function getHiringListData()
    {
        $jobListData = $this->applicationDataDbService->formatJoblistForHiringList();
        $clientData = $this->applicationDataDbService->formatClientsForHiringList();
        $agentData = $this->applicationDataDbService->formatAgentsForHiringList();
        $hiringListData = [
            'jobList' => $jobListData,
            'clients' => $clientData,
            'agents' => $agentData,
        ];
        return apiSuccess($hiringListData, 'fetched');
    }

    public function getApplicationListData()
    {
        $jobListData = $this->applicationDataDbService->formatJoblistForApplicationList();
        $clientData = $this->applicationDataDbService->formatClientsForApplicationList();
        $agentData = $this->applicationDataDbService->formatAgentsForApplicationList();

        $applicationListData = [
            'jobList' => $jobListData,
            'clients' => $clientData,
            'agents' => $agentData,
        ];

        return apiSuccess($applicationListData, 'fetched');
    }

    public function getShortListData()
    {
        $jobListData = $this->applicationDataDbService->formatJoblistForShortList();
        $clientData = $this->applicationDataDbService->formatClientsForShortList();
        $agentData = $this->applicationDataDbService->formatAgentsForShortList();
        $shortListData = [
            'jobList' => $jobListData,
            'clients' => $clientData,
            'agents' => $agentData,
        ];
        return apiSuccess($shortListData, 'fetched');
    }

    public function getWaitingListData()
    {
        $jobListData = $this->applicationDataDbService->formatJoblistForWaitingList();
        $clientData = $this->applicationDataDbService->formatClientsForWaitingList();
        $agentData = $this->applicationDataDbService->formatAgentsForWaitingList();
        $waitingListData = [
            'jobList' => $jobListData,
            'clients' => $clientData,
            'agents' => $agentData,
        ];
        return apiSuccess($waitingListData, 'fetched');
    }

    public function getRejectedListData()
    {
        $jobListData = $this->applicationDataDbService->formatJoblistForRejectedList();
        $clientData = $this->applicationDataDbService->formatClientsForRejectedList();
        $agentData = $this->applicationDataDbService->formatAgentsForRejectedList();
        $rejectedListData = [
            'jobList' => $jobListData,
            'clients' => $clientData,
            'agents' => $agentData,
        ];
        return apiSuccess($rejectedListData, 'fetched');
    }
    public function getEmbassyHints(Request $request)
    {
        $search = $request->get('search', '');
        $submitDate = $request->get('submit_date');
        $excludeEmbassyListId = $request->get('exclude_embassy_list_id');

        $query = Application::with(['embassySubmission', 'processes'])
            ->whereNotNull('passport_no')
            ->whereHas('processes', function ($q) {
                $q->whereJsonContains('data->medical_fit', 'fit');
            })
            ->whereHas('jobList.workOrder.client', function ($q) {
                $q->where('country_id', 10);
            })
            ->whereDoesntHave('currentProcess', function ($q) {
                $q->where('status', 'completed')
                    ->whereHas('process', function ($pq) {
                        $pq->where('name', 'on_boarding');
                    });
            })
            ->when($submitDate, function ($q) use ($submitDate, $excludeEmbassyListId) {
                $q->whereNotIn('passport_no', function ($subQuery) use ($submitDate, $excludeEmbassyListId) {
                    $subQuery->select('embassy_list_items.passport_no')
                        ->from('embassy_list_items')
                        ->join('embassy_lists', 'embassy_lists.id', '=', 'embassy_list_items.embassy_list_id')
                        ->whereDate('embassy_lists.submit_date', $submitDate);

                    if ($excludeEmbassyListId) {
                        $subQuery->where('embassy_lists.id', '!=', (int) $excludeEmbassyListId);
                    }
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where('passport_no', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->limit(20);

        $applications = $query->get();

        return response()->json([
            'success' => true,
            'data' => \App\Modules\Application\Resources\EmbassyHintResource::collection($applications),
        ]);
    }

    public function checkPassportNoExists(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passport_no' => ['required', 'string', 'max:255'],
            'ignore_id' => ['nullable', 'integer'],
        ]);

        $passportNo = trim($validated['passport_no']);

        if ($passportNo === '') {
            return response()->json(['exists' => false]);
        }

        $query = ApplicationModel::query()->where('passport_no', $passportNo);

        if (!empty($validated['ignore_id'])) {
            $query->where('id', '!=', (int) $validated['ignore_id']);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    // New method to extract passport data using Google Vision OCR + Gemini API
    public function PassportOCR(Request $request): JsonResponse
    {
        $request->validate([
            'passport' => 'required|image|mimes:jpeg,png,jpg|max:10240', // max 10MB
        ]);

        $file = $request->file('passport');
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (blank($apiKey)) {
            return response()->json(
                [
                    'error' => 'OpenAI API key is not configured.',
                ],
                500,
            );
        }

        try {
            // Step 1: Use Google Vision API to extract text from passport image
            $imagePath = $file->getRealPath();
            $extractedText = $this->ocrService->extractText($imagePath);

            // return $extractedText;

            if (blank($extractedText)) {
                return response()->json(
                    [
                        'error' => 'No text could be extracted from the passport image.',
                    ],
                    422,
                );
            }

            // Step 2: Send extracted text to ChatGPT for structured output with retry logic
            $url = "{$baseUrl}/chat/completions";

            $maxRetries = 3;
            $attempt = 0;
            $response = null;
            $lastError = null;

            while ($attempt < $maxRetries) {
                $attempt++;

                $response = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                    ])
                    ->timeout(60)
                    ->post($url, [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a passport data extraction assistant. Extract structured data from passport OCR text and return valid JSON only.',
                            ],
                            [
                                'role' => 'user',
                                'content' => $this->passportExtractionPrompt($extractedText),
                            ],
                        ],
                        'response_format' => ['type' => 'json_object'],
                        'temperature' => 0.1,
                    ]);

                // If successful, break out of retry loop
                if ($response->successful()) {
                    break;
                }

                // Check for rate limit or server errors
                $statusCode = $response->status();
                $responseData = $response->json();

                // If it's a rate limit (429) or server error (5xx) and we have retries left, wait and retry
                if (($statusCode === 429 || $statusCode >= 500) && $attempt < $maxRetries) {
                    $lastError = $responseData;
                    sleep(2); // Wait 2 seconds before retrying
                    continue;
                }

                // For other errors or final attempt, store error and break
                $lastError = $responseData;
                break;
            }

            if ($response->failed()) {
                return response()->json(['error' => 'API Error', 'details' => $lastError], 400);
            }

            $result = $response->json();
            $aiText = $result['choices'][0]['message']['content'] ?? null;

            if (!is_string($aiText) || blank($aiText)) {
                return response()->json(
                    [
                        'error' => 'The model returned an empty response.',
                        'details' => $result,
                    ],
                    422,
                );
            }

            $decoded = json_decode($aiText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(
                    [
                        'error' => 'Invalid JSON returned by model.',
                        'details' => $aiText,
                    ],
                    422,
                );
            }

            return response()->json($decoded);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function passportExtractionPrompt(string $extractedText): string
    {
        return <<<PROMPT
        You are an expert OCR data extraction system specialized in passport data.

        Your task is to extract structured information from messy OCR text that may contain:
        - Mixed languages (English, Bengali, Arabic, etc.)
        - Unclear spacing and formatting
        - Labels mixed with values
        - MRZ (Machine Readable Zone) lines starting with P<

        STRICT RULES:
        - Return ONLY one valid JSON object (no explanation, no extra text).
        - Do NOT include markdown formatting or code blocks.
        - Use EXACT field names provided below.
        - If a field is missing, unclear, or not present, return null.
        - Do NOT guess or fabricate data.
        - Keep values exactly as written (original format).
        - Clean up extra spaces and special characters.
        - Prioritize data from MRZ line if available (most reliable).
        - If multiple values exist for a field, choose the clearest one.

        FIELDS TO EXTRACT:
        {
        "surname": string|null,
        "given_name": string|null,
        "full_name": string|null,
        "nationality": string|null,
        "passport_number": string|null,
        "date_of_birth": string|null,
        "sex": string|null,
        "place_of_birth": string|null,
        "date_of_issue": string|null,
        "date_of_expiry": string|null,
        "issuing_authority": string|null,
        "signature": string|null,
        "father_name": string|null,
        "mother_name": string|null,
        "spouse_name": string|null,
        "personal_no": string|null,
        "permanent_address": string|null,
        "emergency_contact_name": string|null,
        "emergency_contact_relationship": string|null,
        "emergency_contact_address": string|null,
        "emergency_contact_telephone": string|null
        }

        SMART EXTRACTION GUIDELINES:

        1. MRZ (Machine Readable Zone) - HIGHEST PRIORITY:
        - Line starts with P<COUNTRY_CODE
        - Format: P<BGDSURNAME<<GIVENNAME<<<<<<
        - Second line contains: PassportNo + Country + DOB(6digits) + Sex + Expiry(6digits) + PersonalNo
        - Example: A037048976BGD9804267M2708263 means:
            * Passport: A03704897 (first 9 chars)
            * Country: BGD
            * DOB: 980426 → 26 APR 1998
            * Sex: M
            * Expiry: 270826 → 26 AUG 2027

        2. Main Passport Fields:
        - surname → Look for "Surname", "BR/Surname", or extract from MRZ before <<
        - given_name → Look for "Given Name", "32/Given Name", or extract from MRZ after <<
        - full_name → If found explicitly (e.g., "Name: FULL NAME"), use it; otherwise combine given_name + surname
        - nationality → Look for "Nationality", "BANGLADESHI", country codes like BGD
        - passport_number → Look for "Passport Number", "R/Passport Number", or alphanumeric starting with letter (e.g., A03704897)
        - date_of_birth → Look for "Date of Birth", "DOB", dates in format DD MMM YYYY or extract from MRZ
        - sex → Look for "M" or "F" standalone, or extract from MRZ
        - place_of_birth → Look for "Place of Birth", city names
        - date_of_issue → Look for "Date of Issue", "Date of de sua", issue dates
        - date_of_expiry → Look for "Date of Expiry", expiry dates, or extract from MRZ
        - issuing_authority → Look for "Issuing Authority", "DIP/DHAKA", "DGHS"
        - signature → Extract if signature text appears (e.g., "Shihaben Mobin Jisan")

        3. Personal Data Section:
        - father_name → Look for "Father's Name:", text after it
        - mother_name → Look for "Mother's Name:", text after it
        - spouse_name → Look for "Spouse's Name:", text after it (may be empty)
        - personal_no → Look for "Personal W", "Personal No", numeric ID, or extract from MRZ
        - permanent_address → Look for "Permanent Address:", extract full address text

        4. Emergency Contact Section:
        - emergency_contact_name → Under "Emergency Contact:" section, look for "Name:"
        - emergency_contact_relationship → Look for "Relationship:" (e.g., BROTHER, FATHER)
        - emergency_contact_address → Look for "Address:" in emergency section
        - emergency_contact_telephone → Look for "Telephone No:", phone numbers starting with +880 or similar

        PARSING TIPS:
        - Ignore non-English text (Bengali, Arabic characters) unless it's part of an address
        - Clean phone numbers (keep format like +8801305953289)
        - Dates: Keep original format (e.g., "26 APR 1998")
        - Addresses: Combine fragmented lines into single address string
        - For empty fields (just colon with no value), return null

        OCR TEXT:
        {$extractedText}

        OUTPUT:
        Return ONLY the JSON object.
        PROMPT;
    }


    public function deleteEmbassySubmission(int $applicationId): JsonResponse
    {
        $deleted = $this->embasySubmissionService->deleteEmbassySubmission($applicationId);

        if (!$deleted) {
            return response()->json(
                [
                    'message' => 'Embassy submission not found',
                ],
                404,
            );
        }

        return response()->json([
            'message' => 'Embassy submission deleted successfully',
        ]);
    }
}
