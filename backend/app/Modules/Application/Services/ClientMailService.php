<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\ClientMail;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\ClientMailRepository;

class ClientMailService extends BaseCachedService
{
    public function __construct(protected ClientMailRepository $repository)
    {
        parent::__construct(new ClientMail());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getClientMail(ClientMail $clientMail)
    {
        return $this->remember(
            $this->byIdCacheKey($clientMail->id),
            fn () => $clientMail
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createClientMail(array $data)
    {
        $clientMail = $this->mutate(fn() => $this->model->create($data));
        return $clientMail;
    }

    public function updateClientMail(ClientMail $clientMail, array $data)
    {
        return $this->mutate(fn() => tap($clientMail)->update($data));
    }

    public function deleteClientMail(ClientMail $clientMail): bool
    {
        return $this->mutate(fn() => $clientMail->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
