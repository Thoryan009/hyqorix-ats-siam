<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Requests\JournalApproveRequest;
use App\Modules\Journals\Requests\JournalIndexRequest;
use App\Modules\Journals\Requests\JournalPayRequest;
use App\Modules\Journals\Requests\JournalStoreRequest;
use App\Modules\Journals\Resources\JournalResource;
use App\Modules\Journals\Services\JournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JournalController extends Controller
{
    public function __construct(
        private readonly JournalService $service
    ) {}

    public function index(JournalIndexRequest $request): AnonymousResourceCollection
    {
        return JournalResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function store(JournalStoreRequest $request): JsonResponse
    {
        $journal = $this->service->create($request->validated());

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

    public function pay(JournalPayRequest $request, Journal $journal): JsonResponse
    {
        $posted = $this->service->pay($journal, $request->validated());

        return apiSuccess(
            new JournalResource($posted),
            'updated',
            200,
            'Journal'
        );
    }
}
