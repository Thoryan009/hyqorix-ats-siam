<?php

namespace App\Modules\Vendor\Services;

use App\Modules\Finance\Services\FinanceAccountService;
use App\Modules\Parties\Services\PartyService;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Repositories\VendorRepository;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\DB;

class VendorService extends BaseCachedService
{
    public function __construct(
        protected VendorRepository $repository,
        protected FinanceAccountService $financeAccountService,
        protected PartyService $partyService,
    ) {
        parent::__construct(new Vendor());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getById(int $id)
    {
        return $this->model->with(['user.roles', 'vendorType', 'createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['vendor_id'] = $this->generateVendorId();
            $user = $this->repository->createUser($data);
            $vendor = $this->repository->createVendor($user->id, $data);
            $this->repository->assignRoles($user, $data['role_id']);
            $this->financeAccountService->ensureVendorAccount($vendor->load('user'));

            if ($this->partyService->shouldCreatePartyAccount($data)) {
                $this->partyService->createFromSourceModule('vendor', $vendor->load('user'));
            }

            $this->flushCache();

            return $vendor->load(['user.roles', 'vendorType', 'createdBy', 'updatedBy']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $vendor = $this->model->with('user')->findOrFail($id);
            $this->repository->updateUser($vendor->user, $data, $vendor);
            $vendor = $this->repository->updateVendor($vendor, $data);
            $this->repository->assignRoles($vendor->user, $data['role_id']);
            $this->financeAccountService->ensureVendorAccount($vendor->load('user'));
            $this->flushCache();

            return $vendor->load(['user.roles', 'vendorType', 'createdBy', 'updatedBy']);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $vendor = $this->model->with('user')->findOrFail($id);
            $vendor->delete();
            $vendor->user?->delete();
            $this->flushCache();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $vendors = $this->model->with('user')->whereIn('id', $ids)->get();

            foreach ($vendors as $vendor) {
                $vendor->delete();
                $vendor->user?->delete();
            }

            $this->flushCache();

            return $vendors->count();
        });
    }

    public function getByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get(['id', 'vendor_image_path']);
    }

    public function generateVendorId(): string
    {
        $lastVendor = $this->model->orderBy('id', 'desc')->first();
        $lastNumber = $lastVendor ? $this->extractTrailingNumber($lastVendor->vendor_id) : 0;

        return 'VND'.str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    private function extractTrailingNumber(?string $value): int
    {
        if ($value && preg_match('/(\d+)\s*$/', trim($value), $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
