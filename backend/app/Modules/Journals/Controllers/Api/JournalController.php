<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Requests\JournalApproveRequest;
use App\Modules\Journals\Requests\JournalIndexRequest;
use App\Modules\Journals\Requests\JournalEntryChatRequest;
use App\Modules\Journals\Requests\JournalNarrationHintRequest;
use App\Modules\Journals\Requests\JournalPayRequest;
use App\Modules\Journals\Requests\JournalResubmitRequest;
use App\Modules\Journals\Requests\JournalReverseRequest;
use App\Modules\Journals\Requests\JournalReturnRequest;
use App\Modules\Journals\Requests\JournalStoreRequest;
use App\Modules\Journals\Resources\JournalResource;
use App\Modules\Journals\Services\JournalEntryChatService;
use App\Modules\Journals\Services\JournalNarrationHintService;
use App\Modules\Journals\Services\JournalService;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JournalController extends Controller
{
    public function __construct(
        private readonly JournalService $service,
        private readonly JournalNarrationHintService $narrationHintService,
        private readonly JournalEntryChatService $entryChatService,
    ) {}

    public function index(JournalIndexRequest $request): AnonymousResourceCollection
    {
        return JournalResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function jobOptions(): JsonResponse
    {
        return apiSuccess($this->service->getJobOptions());
    }

    public function demandLetterOptions(): JsonResponse
    {
        return apiSuccess($this->service->getDemandLetterOptions());
    }

    public function narrationHints(JournalNarrationHintRequest $request): JsonResponse
    {
        return apiSuccess([
            'hints' => $this->narrationHintService->generate($request->context()),
        ]);
    }

    public function entryChat(JournalEntryChatRequest $request): JsonResponse
    {
        try {
            return apiSuccess([
                'reply' => $this->entryChatService->chat($request->chatMessages()),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function store(JournalStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data);

        $journal = $this->service->create($data);

        return apiSuccess(
            new JournalResource($journal),
            'created',
            201,
            'Journal'
        );
    }

    public function show(Journal $journal): JournalResource
    {
        return new JournalResource(
            $this->service->getJournal($journal)
        );
    }

    public function approve(JournalApproveRequest $request, Journal $journal): JsonResponse
    {
        $approved = $this->service->approve(
            $journal,
            (string) $request->validated('manager_comment')
        );

        return apiSuccess(
            new JournalResource($approved),
            'updated',
            200,
            'Journal'
        );
    }

    public function return(JournalReturnRequest $request, Journal $journal): JsonResponse
    {
        $returned = $this->service->return(
            $journal,
            (string) $request->validated('manager_comment')
        );

        return apiSuccess(
            new JournalResource($returned),
            'updated',
            200,
            'Journal'
        );
    }

    public function resubmit(JournalResubmitRequest $request, Journal $journal): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data, $journal);

        $resubmitted = $this->service->resubmit($journal, $data);

        return apiSuccess(
            new JournalResource($resubmitted),
            'updated',
            200,
            'Journal'
        );
    }

    public function pay(JournalPayRequest $request, Journal $journal): JsonResponse
    {
        $data = $request->validated();
        $this->applyStoredReceiptPaths($request, $data, $journal);

        $posted = $this->service->pay($journal, $data);

        return apiSuccess(
            new JournalResource($posted),
            'updated',
            200,
            'Journal'
        );
    }

    public function reverse(JournalReverseRequest $request, Journal $journal): JsonResponse
    {
        $reversed = $this->service->reverse($journal);

        return apiSuccess(
            new JournalResource($reversed),
            'updated',
            200,
            'Journal'
        );
    }

    private function applyStoredReceiptPaths($request, array &$data, ?Journal $journal = null): void
    {
        if (!$request->hasFile('receipt_path')) {
            unset($data['receipt_path']);

            return;
        }

        $files = $request->file('receipt_path');
        if (!is_array($files)) {
            $files = [$files];
        }

        $paths = [];
        foreach ($files as $file) {
            if ($file) {
                $paths[] = FileHelper::store($file, 'journal-receipts');
            }
        }

        if ($paths === []) {
            unset($data['receipt_path']);

            return;
        }

        if ($journal) {
            $existing = $journal->receipt_path ?? [];
            $data['receipt_path'] = array_values(array_merge($existing, $paths));

            return;
        }

        $data['receipt_path'] = $paths;
    }
}
