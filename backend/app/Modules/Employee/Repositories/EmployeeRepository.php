<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use App\Repositories\BaseRepository;

class EmployeeRepository extends BaseRepository
{
    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    public function getEmployeeDashboardData(array $filters = [])
    {
        $query = $this->model->newQuery();
        $this->applyFilters($query, $filters);

        // join users to access status and use correlated subquery for tasks
        $query->leftJoin('users', 'employees.user_id', '=', 'users.id');

        return $query->selectRaw('
            COUNT(*) as total_employees,

            SUM(CASE WHEN users.status = "active" THEN 1 ELSE 0 END) as active_employees_count,
            SUM(CASE WHEN users.status = "inactive" THEN 1 ELSE 0 END) as inactive_employees_count,

            -- TODAY JOINED
            SUM(CASE WHEN DATE(employees.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_joined_count,

            -- EMPLOYEES WITH TASKS (assigned tasks > 0)
            SUM(CASE WHEN (SELECT COUNT(*) FROM tasks WHERE tasks.assigned_to = users.id) > 0 THEN 1 ELSE 0 END) as employees_with_tasks_count
        ')->first();
    }

    /**
     * Apply all filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDesignationFilter($query, $filters['designation_id'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyRoleFilter($query, $filters['role_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    /**
     * Search by name, email, phone (users table)
     */
    protected function applySearch(
        Builder $query,
        ?string $search
    ): void {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->whereHas('user', function (Builder $q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp_no', 'like', "%{$search}%");
            });
        });
    }


    /**
     * Optional filter for country_id
     */
    protected function applyDesignationFilter(Builder $query, ?int $designationId): void
    {
        if (!$designationId) {
            return;
        }

        $query->where('designation_id', $designationId);
    }

    /**
     * Optional filter for status (users table)
     */
    protected function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (!$status) {
            return;
        }
        $query->whereHas('user', function (Builder $q) use ($status) {
            $q->where('status', $status);
        });
    }
    /**
     * Optional filter for role_id (users & roles tables)
     */
    protected function applyRoleFilter(Builder $query, ?int $roleId): void
    {
        if (!$roleId) {
            return;
        }
        $query->whereHas('user.roles', function (Builder $q) use ($roleId) {
            $q->where('roles.id', $roleId);
        });
    }

    /**
     * Apply from/to date filters
     */


    public function createUser(array $data)
    {
        return \App\Modules\Auth\Models\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'password' => Hash::make($data['password']),
            'status'   => $data['status'] ?? 'active',
            'type'     => 'employee',
        ]);
    }

    public function updateUser($user, array $data): void
    {
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'status' => $data['status'],
            'type' => 'employee',
        ]);
        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }
    }

    public function createEmployee(int $userId, array $data)
    {
        return $this->model->create([
            'user_id'        => $userId,
            'designation_id' => $data['designation_id'],
            'image_path'     => $data['image_path'] ?? null,
            'username'       => $data['username'] ?? null,
            'send_credentials' => $data['send_credentials'] ?? '0',
            'show_ats_summary' => $data['show_ats_summary'] ?? '1',
            'manager_approval' => $data['manager_approval'] ?? '0',
        ]);
    }

    public function assignRoles($user, array $roleIds)
    {
        return $user->roles()->sync($roleIds);
    }

    public function assignDepartments($employee, array $departmentIds)
    {
        return $employee->departments()->sync($departmentIds);
    }
    public function updateEmployee($employee, array $data): void
    {
        $employee->update([
            'designation_id' => $data['designation_id'],
            'image_path'     => $data['image_path'] ?? $employee->image_path,
            'username'       => $data['username'] ?? null,
            'send_credentials' => $data['send_credentials'] ?? '0',
            'show_ats_summary' => $data['show_ats_summary'] ?? '1',
            'manager_approval' => $data['manager_approval'] ?? '0',
        ]);
    }

    public function getApprovalManagers()
    {
        return $this->model->with(['user.roles', 'designation', 'departments'])
            ->where('manager_approval', 1)
            ->whereHas('user', fn ($query) => $query->where('status', 1))
            ->get();
    }

    public function getAllEmployeesWithPoints()
    {
        return $this->model->with('user:id,name')
            ->orderBy('points', 'desc')
            ->get();

    }

    public function getTopEmployeeByPoints()
    {
        return $this->model->with(['user:id,name', 'designation:id,name'])
            ->orderByDesc('points')
            ->first();
    }
}
