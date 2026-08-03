<?php

namespace App\Modules\Country\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Country\Models\CountryDetails;
use App\Modules\Country\Requests\CountryDetailsRequest;
use App\Modules\Country\Resources\CountryDetailsResource;
use App\Modules\Country\Services\CountryDetailsService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryDetailsController extends Controller
{
    public function __construct(
        private readonly CountryDetailsService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $countryDetailsData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return CountryDetailsResource::collection($countryDetailsData);
    }

    public function store(CountryDetailsRequest $request): JsonResponse
    {
        $countryDetails = $this->service->createCountryDetails($request->validated());

        return apiSuccess(
            new CountryDetailsResource($countryDetails),
            'created'
        );
    }

    public function show(CountryDetails $countryDetails): CountryDetailsResource
    {
        return new CountryDetailsResource(
            $this->service->getCountryDetails($countryDetails)
        );
    }

    public function update(CountryDetailsRequest $request, CountryDetails $countryDetails): JsonResponse
    {
        $countryDetails = $this->service->updateCountryDetails(
            $countryDetails,
            $request->validated()
        );

        return apiSuccess(
            new CountryDetailsResource($countryDetails),
            'updated'
        );
    }

    public function destroy(CountryDetails $countryDetails): JsonResponse
    {
        $this->service->deleteCountryDetails($countryDetails);

        return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);

        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }
}
