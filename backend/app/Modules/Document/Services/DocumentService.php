<?php

namespace App\Modules\Document\Services;

use App\Modules\Document\Models\Document;
use App\Modules\Document\Repositories\DocumentRepository;
use App\Services\BaseCachedService;

class DocumentService extends BaseCachedService
{
    public function __construct(protected DocumentRepository $repository)
    {
        parent::__construct(new Document());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getById(int $id): Document
    {
        return $this->remember(
            $this->byIdCacheKey($id),
            fn () => $this->model->with(['createdBy', 'updatedBy'])->findOrFail($id)
        );
    }

    public function getByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get(['id', 'path']);
    }

    public function create(array $data): Document
    {
        return $this->mutate(function () use ($data) {
            $data['document_no'] = $this->generateDocumentNo();

            return $this->model->create($data);
        });
    }

    public function update(int $id, array $data): Document
    {
        return $this->mutate(function () use ($id, $data) {
            $document = $this->model->findOrFail($id);
            $document->update($data);

            return $document->fresh(['createdBy', 'updatedBy']);
        });
    }

    public function delete(int $id): bool
    {
        return $this->mutate(fn () => (bool) $this->model->findOrFail($id)->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }

    public function categories(): array
    {
        return config('document.categories', []);
    }

    public function generateDocumentNo(): string
    {
        $lastDocument = $this->model->orderByDesc('id')->first();
        $lastNumber = 0;

        if ($lastDocument && preg_match('/DOC-(\d+)/', $lastDocument->document_no, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return 'DOC-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
