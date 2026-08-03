<?php

namespace App\Modules\Document\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Requests\DocumentRequest;
use App\Modules\Document\Resources\DocumentResource;
use App\Modules\Document\Services\DocumentService;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['category'] = $request->get('category');

        return DocumentResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function categories(): JsonResponse
    {
        return apiSuccess($this->service->categories());
    }

    public function store(DocumentRequest $request): JsonResponse
    {
        $storedFiles = [];

        try {
            $data = $request->validated();
            unset($data['document_file']);

            $path = FileHelper::store($request->file('document_file'), 'documents');
            $data['path'] = $path;
            $storedFiles[] = $path;

            $document = $this->service->create($data);

            return apiSuccess(new DocumentResource($document), 'created');
        } catch (\Exception $e) {
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e;
        }
    }

    public function show(Document $document): DocumentResource
    {
        return new DocumentResource($this->service->getById($document->id));
    }

    public function update(DocumentRequest $request, Document $document): JsonResponse
    {
        $storedFiles = [];

        try {
            $data = $request->validated();
            unset($data['document_file']);

            $record = $this->service->getById($document->id);

            if ($request->hasFile('document_file')) {
                if ($record->path) {
                    FileHelper::delete($record->path);
                }

                $path = FileHelper::store($request->file('document_file'), 'documents');
                $data['path'] = $path;
                $storedFiles[] = $path;
            }

            $updated = $this->service->update($document->id, $data);

            return apiSuccess(new DocumentResource($updated), 'updated');
        } catch (\Exception $e) {
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e;
        }
    }

    public function destroy(Document $document): JsonResponse
    {
        $record = $this->service->getById($document->id);

        if ($record->path) {
            FileHelper::delete($record->path);
        }

        $this->service->delete($document->id);

        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $records = $this->service->getByIds($request->ids);

        foreach ($records as $record) {
            if ($record->path) {
                FileHelper::delete($record->path);
            }
        }

        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function download(Document $document)
    {
        $record = $this->service->getById($document->id);

        if (!$record->path || !Storage::disk('public')->exists($record->path)) {
            abort(404, 'Document file not found.');
        }

        return Storage::disk('public')->download($record->path, $record->file_name);
    }
}
