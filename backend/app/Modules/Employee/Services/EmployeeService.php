<?php

namespace App\Modules\Employee\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Repositories\EmployeeRepository;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\Mail;
use App\Modules\Employee\Mail\EmployeeMail;
use App\Modules\Setting\Models\Setting;
use App\Modules\Shared\Helpers\FileHelper;

class EmployeeService extends BaseCachedService
{
    public function __construct(protected EmployeeRepository $repository)
    {
        parent::__construct(new Employee());
        $this->repository = $repository;
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember($this->filtersCacheKey($filters), fn() => $this->repository->getPaginatedData($filters));
    }

    public function getEmployeeDashboardData(array $filters = [])
    {
        $filters['employee_dashboard_data'] = 'employee_dashboard_data';

        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getEmployeeDashboardData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById(int $id)
    {
        return $this->remember($this->byIdCacheKey($id), fn() => $this->model->findOrFail($id));
    }

    public function create($request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated(); // array
            $storedFiles = [];
            $user = $this->repository->createUser($data);

        try {

                if ($request->hasFile('image_path')) {
                    $path = FileHelper::store($request->file('image_path'), 'employee-images');
                    $data['image_path'] = $path;
                    $storedFiles['image_path'] = $path;
                }

                $employee = $this->repository->createEmployee($user->id, $data);
                $this->repository->assignRoles($user, $data['role_ids']);
                $this->repository->assignDepartments($employee, $data['department_ids']);

                if ($data['send_credentials'] == '1') {
                    $setting = Setting::first();
                    $mailData = [
                        'employee' => $data,
                        'login_url' => config('app.frontend_url') . '/login',
                        'setting' => $setting,
                    ];
                    Mail::to($user->email)->send(new EmployeeMail($mailData));
                }

                $this->flushCache();
                return $employee;
            } catch (\Exception $e) {
                // Rollback transaction and delete any stored files
                foreach ($storedFiles as $file) {
                    FileHelper::delete($file);
                }
                throw $e; // Re-throw the exception after cleanup
            }
        });
    }

    public function update(int $id, $request)
    {
        return DB::transaction(function () use ($id, $request) {

            $data = $request->validated();
            $storedFiles = [];

            $employee = $this->model->with('user')->findOrFail($id);

            try {
                // 🔥 FILE HANDLE (same as create কিন্তু update logic সহ)

                if ($request->hasFile('image_path')) {

                    // 👉 OLD FILE DELETE (important)
                    if ($employee->image_path) {
                        FileHelper::delete($employee->image_path);
                    }

                    // 👉 NEW FILE STORE
                    $path = FileHelper::store($request->file('image_path'), 'employee-images');

                    $data['image_path'] = $path;
                    $storedFiles['image_path'] = $path;
                }


                // 🔥 UPDATE USER + EMPLOYEE
                $this->repository->updateUser($employee->user, $data);
                $this->repository->updateEmployee($employee, $data);



                // 🔥 ROLES
                $this->repository->assignRoles($employee->user, $data['role_ids']);
                // 🔥 DEPARTMENTS
                $this->repository->assignDepartments($employee, $data['department_ids']);

                // 🔥 MAIL (same as before)
                if (!empty($data['send_credentials']) && $data['send_credentials'] == '1') {

                    $setting = Setting::first();
                    $user = $employee->user->fresh();

                    $mailData = [
                        'employee' => [
                            'name' => $user->name,
                            'email' => $user->email,
                            'password' => $data['password'] ?? null,
                        ],
                        'login_url' => config('app.frontend_url') . '/login',
                        'setting' => $setting,
                    ];

                    Mail::to($user->email)->send(new EmployeeMail($mailData));
                }

                $this->flushCache();

                return $employee;

            } catch (\Exception $e) {

                // ❗ NEW UPLOADED FILE rollback
                foreach ($storedFiles as $file) {
                    FileHelper::delete($file);
                }

                throw $e;
            }
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {

            $employee = $this->model->with('user')->findOrFail($id);

            // -----------------------------
            // DELETE FILE FIRST
            // -----------------------------
            if (!empty($employee->image_path)) {
                FileHelper::delete($employee->image_path);
            }

            // -----------------------------
            // DELETE DB RECORDS
            // -----------------------------
            $employee->delete();
            $employee->user?->delete();

            $this->flushCache();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {

            // -----------------------------
            // GET FILES FIRST
            // -----------------------------
            $employees = $this->model
                ->whereIn('id', $ids)
                ->get(['id', 'image_path']);

            $filePaths = $employees->pluck('image_path')->filter();

            // -----------------------------
            // DELETE FILES
            // -----------------------------
            foreach ($filePaths as $file) {
                FileHelper::delete($file);
            }

            // -----------------------------
            // DELETE DB
            // -----------------------------
            $deletedCount = $this->model->whereIn('id', $ids)->delete();

            $this->flushCache();

            return $deletedCount;
        });
    }

    public function getApprovalManagers()
    {
        return $this->remember(
            $this->filtersCacheKey(['approval_managers' => 1]),
            fn() => $this->repository->getApprovalManagers()
        );
    }

    public function getAllEmployeesWithPoints()
    {
        return $this->repository->getAllEmployeesWithPoints();
    }

    public function getTopEmployeeByPoints()
    {
        return $this->repository->getTopEmployeeByPoints();
    }
}
