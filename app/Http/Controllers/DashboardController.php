<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index()
    {
        $statistics = $this->dashboardService->getStatistics();

        return view('dashboard', $statistics);
    }
}
