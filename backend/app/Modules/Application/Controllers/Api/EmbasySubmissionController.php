<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Models\EmbasySubmission;
use App\Modules\Application\Requests\EmbasySubmissionRequest;
use App\Modules\Application\Resources\EmbasySubmissionResource;
use App\Modules\Application\Services\EmbasySubmissionService;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Contracts\EmbasySubmissionDataServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Application\Models\Application as ApplicationModel;
use App\Modules\Application\Resources\ApplicationResource;
use Illuminate\Support\Facades\DB;

class EmbasySubmissionController extends Controller
{
    public function __construct(private readonly EmbasySubmissionService $service, private readonly EmbasySubmissionDataServiceInterface $dataService) {}
   public function getKSAData()
    {
        $ksaData = $this->dataService->formatKSAData();
        return apiSuccess($ksaData, 'fetched');
    }
    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {

        $filters = $request->filters();
        $filters['work_order_id'] = (int) $request->get('work_order_id', null);
        $filters['job_list_id'] = (int) $request->get('job_list_id', null);
        $filters['client_id'] = (int) $request->get('client_id', null);
        $filters['process_id'] = (int) $request->get('process_id', null);
        $filters['status'] = $request->get('status', null);
        $filters['has_embassy_submission'] = $request->get('has_embassy_submission', null);
        $filters['agent_id'] = (int) $request->get('agent_id', null);
        $filters['country_id'] = 10;

        $user = auth()->user();
        if ($user && $user->type === 'agent') {
            $agentApplicationIds = $user->agent?->applications()->pluck('id')->toArray() ?? [];
            $filters['application_ids'] = $agentApplicationIds;
        }

        // Apply reusable client filter

        // Fetch data
        $data = $this->service->getPaginatedDataWithCache($filters);

        return ApplicationResource::collection($data);
    }

    public function updateEmbassySubmission(EmbasySubmissionRequest $request, int $applicationId): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if (blank($data['visa_profession_en'] ?? null) && filled($data['visa_profession_ar'] ?? null)) {
                $data['visa_profession_en'] = $this->generateVisaProfessionEnglishText($data['visa_profession_ar']);
            }

            // ensure application exists
            $application = ApplicationModel::findOrFail($applicationId);

            // create or update
            $embassySubmission = $this->service->upsertEmbassySubmission($application->id, $data);

         $currentStatus = $application->tasheer_status;

        if ($embassySubmission->mofa_no) {

            if (is_null($currentStatus)) {
                $application->update([
                    'tasheer_status' => 'pending',
                ]);
            }

        } else {

            if ($currentStatus === 'pending') {
                $application->update([
                    'tasheer_status' => null,
                ]);
            }
        }

            DB::commit();

            return response()->json([
                'message' => 'Embassy submission saved successfully',
                'data' => $embassySubmission,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(
                [
                    'message' => 'Failed to update embassy submission',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function generateVisaProfessionEnglish(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visa_profession_ar' => ['required', 'string'],
        ]);

        return response()->json([
            'message' => 'Visa profession translated successfully',
            'data' => [
                'visa_profession_en' => $this->generateVisaProfessionEnglishText($data['visa_profession_ar']),
            ],
        ]);
    }

    private function generateVisaProfessionEnglishText(string $arabicProfession): ?string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (blank($apiKey) || blank($arabicProfession)) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->timeout(15)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You translate Arabic visa profession/job titles into concise natural English. Return only the translated profession text.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Arabic visa profession: {$arabicProfession}",
                        ],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 32,
                ]);

            if ($response->failed()) {
                return null;
            }

            $result = $response->json();
            $aiText = $result['choices'][0]['message']['content'] ?? null;

            if (!is_string($aiText)) {
                return null;
            }

            return trim(trim($aiText), "\"'");
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getMofaInformations(int $applicationId): JsonResponse
    {
        $data = $this->service->getMofaInformations($applicationId);

        $application = $data['application'];
        $mofaInformations = $data['mofaInformations'];

        \Log::info('Mofa Informations', [
            'application_id' => $applicationId,
            'mofaInformations' => $mofaInformations,
        ]);

        if ($mofaInformations->isEmpty()) {
            \Log::warning('No Mofa Informations found', [
                'application_id' => $applicationId,
            ]);

            return apiSuccess(
                [
                    new EmbasySubmissionResource(
                        (object) [
                            'application' => $application,
                        ],
                    ),
                ],
                'fetched',
                200,
                'Application ID with no Mofa Informations',
            );
        }

        return apiSuccess(EmbasySubmissionResource::collection($mofaInformations), 'fetched', 200, 'Mofa Informations');
    }

    public function deleteEmbassySubmission(int $applicationId): JsonResponse
    {
        $deleted = $this->service->deleteEmbassySubmission($applicationId);

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

    public function bulkStatusUpdate(Request $request): JsonResponse
    {
    
        $data = $request->all();

        $updatedCount = $this->service->bulkStatusUpdate($data['ids'], $data['status']);

        return response()->json([
            'message' => "Status updated to '{$data['status']}' for {$updatedCount} embassy submissions",
        ]);
    }


}
