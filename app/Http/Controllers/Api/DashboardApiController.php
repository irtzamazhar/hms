<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'summary'  => $this->service->getSummary(),
            'revenue'  => $this->service->getRevenueChart(14),
            'growth'   => $this->service->getPatientGrowth(6),
            'activity' => $this->service->getTodayActivity(),
        ]);
    }
}
