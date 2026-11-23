<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analyticsService): View
    {
        $metrics = $analyticsService->getDashboardMetrics();

        return view('admin.dashboard', [
            'metrics' => $metrics,
        ]);
    }
}
