<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analyticsService, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();
        $metrics = $analyticsService->getDashboardMetrics();

        return view('admin.dashboard', [
            'metrics' => $metrics,
        ]);
    }
}
