<?php

namespace App\Modules\Journals\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Requests\JournalIndexRequest;
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
}
