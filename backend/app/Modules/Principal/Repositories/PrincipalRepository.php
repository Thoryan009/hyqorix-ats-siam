<?php

namespace App\Modules\Principal\Repositories;

use App\Modules\Principal\Models\Principal;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class PrincipalRepository extends BaseRepository
{
    public function __construct(Principal $model)
    {
        parent::__construct($model);
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with('user');
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyPrincipalFilter($query, $filters['principal_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('principal_id', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp_no', 'like', "%{$search}%");
                });
        });
    }

    protected function applyPrincipalFilter(Builder $query, ?int $principalId): void
    {
        if (!$principalId)
            return;

        $query->where('id', $principalId);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->join('users', 'principals.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->select('principals.*');
    }


    public function createUser(array $data)
    {
        return \App\Modules\Auth\Models\User::create([
            'name' => $data['organization_name'],
            'email' => $data['email'],
            'phone' => $data['contact_no'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'password' => Hash::make($data['password']),
            'type' => 'principal',
            'status' => $data['status'] ?? 1,
        ]);
    }

    public function createPrincipal($userId, array $data)
    {
        $principal = Principal::create([
            'user_id' => $userId,
            'principal_id' => $data['principal_id'],
            'address' => $data['address'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'contact_person_name' => $data['contact_person_name'],
            'designation' => $data['designation'],
            'send_notification' => $data['send_notification'] ?? 1,
            'contact_person_no' => $data['contact_person_no'] ?? null,
        ]);
        $this->trackCreatePrincipal($principal);

        return $principal;
    }

    /**
     * Update an existing user
     */
    public function updateUser($user, array $data, $principal)
    {
        $updateData = [
            'name' => $data['organization_name'],
            'email' => $data['email'],
            'phone' => $data['contact_no'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'status' => $data['status'] ?? $user->status,
            'type' => 'principal',
        ];

        $this->trackUpdatePrincipal($principal);

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
    }
    public function updatePrincipal($principal, $data)
    {
        $principal->update([
            'address' => $data['address'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'contact_person_name' => $data['contact_person_name'],
            'designation' => $data['designation'],
            'contact_person_no' => $data['contact_person_no'] ?? null,
            'send_notification' => $data['send_notification'] ?? 1,
        ]);
        return $principal;
    }

    private function trackUpdatePrincipal($principal)
    {
        $principal->updated_by = auth()->id();
        $principal->save();
    }

    private function trackCreatePrincipal($principal)
    {
        $principal->created_by = auth()->id();
        $principal->save();
    }

    public function assignRoles($user, int $roleId)
    {
        return $user->roles()->sync($roleId);
    }
}
