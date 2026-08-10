<?php

namespace App\Modules\Vendor\Repositories;

use App\Modules\Vendor\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with(['user.roles', 'vendorType', 'createdBy', 'updatedBy']);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('vendor_id', 'like', "%{$search}%")
                ->orWhere('organization_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $q2) use ($search) {
                    $q2->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp_no', 'like', "%{$search}%");
                });
        });
    }

    protected function applyStatusFilter(Builder $query, $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->whereHas('user', function (Builder $q) use ($status) {
            $q->where('status', $status);
        });
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('organization_name', 'asc');
    }

    public function createUser(array $data)
    {
        return \App\Modules\Auth\Models\User::create([
            'name' => $data['organization_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'password' => Hash::make($data['password']),
            'type' => 'vendor',
            'status' => $data['status'] ?? 1,
        ]);
    }

    public function createVendor(int $userId, array $data): Vendor
    {
        $vendor = Vendor::create([
            'user_id' => $userId,
            'vendor_id' => $data['vendor_id'],
            'organization_name' => $data['organization_name'],
            'vendor_type' => $data['vendor_type'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'address' => $data['address'] ?? null,
            'vendor_image_path' => $data['vendor_image_path'] ?? null,
            'send_notification' => $data['send_notification'] ?? 1,
        ]);

        $this->trackCreateVendor($vendor);

        return $vendor;
    }

    public function updateUser($user, array $data, Vendor $vendor): void
    {
        $updateData = [
            'name' => $data['organization_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'status' => $data['status'] ?? $user->status,
            'type' => 'vendor',
        ];

        $this->trackUpdateVendor($vendor);

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $vendor->update([
            'organization_name' => $data['organization_name'],
            'vendor_type' => $data['vendor_type'] ?? $vendor->vendor_type,
            'contact_person' => $data['contact_person'] ?? null,
            'address' => $data['address'] ?? null,
            'vendor_image_path' => $data['vendor_image_path'] ?? $vendor->vendor_image_path,
            'send_notification' => $data['send_notification'] ?? $vendor->send_notification,
        ]);

        return $vendor;
    }

    private function trackUpdateVendor(Vendor $vendor): void
    {
        $vendor->updated_by = auth()->id();
        $vendor->save();
    }

    private function trackCreateVendor(Vendor $vendor): void
    {
        $vendor->created_by = auth()->id();
        $vendor->save();
    }

    public function assignRoles($user, int $roleId)
    {
        return $user->roles()->sync($roleId);
    }
}
