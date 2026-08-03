<?php
namespace App\Modules\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\DashboardService;
use App\Modules\Auth\Resources\DashboardResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function index()
    {
        $data = $this->service->getDashboardData();

        return apiSuccess(
            new DashboardResource($data),
            'Dashboard data fetched successfully'
        );
    }

    public function flightSummary(Request $request)
    {
        $filters = [
            'flight_no' => $request->input('search'),
            'client' => $request->input('client_id'),
            'country' => $request->input('country_id'),
            'departure_from' => $request->input('departure_from'),
            'departure_to' => $request->input('departure_to'),
        ];
        $data = $this->service->getFlightSummary($filters);

        return apiSuccess(
            $data,
            'Flight summary fetched successfully',
            200,
            'Flight summary'
        );
    }
}
