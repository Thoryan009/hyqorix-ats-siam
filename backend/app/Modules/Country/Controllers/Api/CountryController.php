<?php

namespace App\Modules\Country\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Client\Services\ClientDataDbService;
use App\Modules\Country\Requests\CountryRequest;
use App\Modules\Country\Services\CountryService;
use App\Modules\Country\Resources\CountryResource;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    public function __construct(private CountryService $service, private ClientDataDbService $clientDataDbService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $data = $this->service->getPaginatedDataWithCache($filters);
        return CountryResource::collection($data);
    }

    public function store(CountryRequest $request)
    {
        $storedFiles = []; // Keep track of stored files in case we need to rollback
        try {
            $data = $request->validated(); // Get validated input

            if ($request->hasFile('country_image_path')) {
                $path = FileHelper::store($request->file('country_image_path'), 'countries');
                $data['country_image_path'] = $path;
                $storedFiles['country_image_path'] = $path;
            }

            $country = $this->service->create($data);
            $this->clientDataDbService->clearClientDataCache();
            return apiSuccess(new CountryResource($country), 'created');
        } catch (\Exception $e) {
            // Rollback any stored files
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }
            throw $e;
        }
    }

    public function show($id)
    {
        return new CountryResource($this->service->getById($id));
    }



    public function update(CountryRequest $request, $id)
{
    $storedFiles = [];
    try {
        $data = $request->validated();

        $record = $this->service->getById($id); // get old record

        // Handle new file upload
        if ($request->hasFile('country_image_path')) {

            // delete old image if exists
            if (!empty($record->country_image_path)) {
                FileHelper::delete($record->country_image_path);
            }

            // store new image
            $path = FileHelper::store(
                $request->file('country_image_path'),
                'countries'
            );

            $data['country_image_path'] = $path;
            $storedFiles['country_image_path'] = $path;
        }

        $updated = $this->service->update($id, $data);

        $this->clientDataDbService->clearClientDataCache();

        return apiSuccess(new CountryResource($updated), 'updated');

    } catch (\Exception $e) {
        // rollback newly uploaded files
        foreach ($storedFiles as $filePath) {
            FileHelper::delete($filePath);
        }

        throw $e;
    }
}
public function destroy($id)
{
    try {
        $record = $this->service->getById($id);

        // delete file before deleting record
        if ($record->country_image_path) {
            FileHelper::delete($record->country_image_path);
        }

        $this->service->delete($id);

        $this->clientDataDbService->clearClientDataCache();

        return apiSuccess(null, 'deleted');

    } catch (\Exception $e) {
        throw $e;
    }
}

public function bulkDelete(Request $request)
{
    try {
        $records = $this->service->getByIds($request->ids);

        // delete all files first
        foreach ($records as $record) {
            if ($record->country_image_path) {
                FileHelper::delete($record->country_image_path);
            }
        }

        $this->service->bulkDelete($request->ids);

        $this->clientDataDbService->clearClientDataCache();

        return apiSuccess(null, 'deleted', 200, 'Records');

    } catch (\Exception $e) {
        throw $e;
    }
}
}
