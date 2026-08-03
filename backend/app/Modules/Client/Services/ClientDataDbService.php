<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Contracts\ClientDataServiceInterface;
use App\Modules\Country\Models\Country;
use Illuminate\Support\Facades\Cache;
use App\Modules\Auth\Models\Role;

class ClientDataDbService implements ClientDataServiceInterface
{
    public $cacheKey = 'client_data_all';
    public function getClientData(): array
    {
        $cacheKey = $this->cacheKey;
        $cacheTTL = 3600;
        return Cache::remember($cacheKey, $cacheTTL, function () {
            $countries = Country::query()
                ->get()
                ->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                    ];
                })
                ->toArray();

            $filterCountries = Country::query()
                ->whereHas('clients')
                ->withCount('clients')
                ->orderBy('name')
                ->get()
                ->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                        'clients_count' => $country->clients_count,
                    ];
                })
                ->toArray();

            $roles = Role::query(); // fetch from DB
            $roles = $roles
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })
                ->toArray();

            return [
                'countries' => $countries,
                'filter_countries' => $filterCountries,
                'roles' => $roles,
            ];
        });
    }

    // public function getEmployeeData(): array
    // {
    //     $cacheKey = $this->cacheKey;

    //     $cacheTTL = 3600;

    //     return Cache::remember($cacheKey, $cacheTTL, function () {
    //         $roles = Role::query(); // fetch from DB
    //         $roles = $roles
    //             ->get()
    //             ->map(function ($role) {
    //                 return [
    //                     'id' => $role->id,
    //                     'name' => $role->name,
    //                 ];
    //             })
    //             ->toArray();

    //         $designations = Designation::query()
    //             ->get()
    //             ->map(function ($designation) {
    //                 return [
    //                     'id' => $designation->id,
    //                     'name' => $designation->name,
    //                 ];
    //             })
    //             ->toArray();

    //         $statuses = User::STATUSES;
    //         $statuses = collect($statuses)
    //             ->map(function ($status) {
    //                 return [
    //                     'id' => $status,
    //                     'name' => $status == 1 ? 'Active' : 'Inactive',
    //                 ];
    //             })
    //             ->toArray();

    //         return [
    //             'roles' => $roles,
    //             'designations' => $designations,
    //             'statuses' => $statuses,
    //         ];
    //     });
    // }
    public function clearClientDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
