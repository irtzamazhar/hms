<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function index()
    {
        $summary  = $this->service->getSummary();
        $revenue  = $this->service->getRevenueChart(14);
        $growth   = $this->service->getPatientGrowth(6);
        $activity = $this->service->getTodayActivity();

        return view('dashboard', compact('summary', 'revenue', 'growth', 'activity'));
    }
}
