<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Request $request, InventoryService $inventoryService): View
    {
        $event = Event::with(['categories' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order');
        }])->where('slug', 'sagar-kinare-2026')->firstOrFail();

        $categoriesWithAvailability = $event->categories->map(function ($category) use ($inventoryService) {
            $category->available = $inventoryService->getAvailableCapacity($category);
            return $category;
        });

        $globalRemaining = $inventoryService->getGlobalRemainingCapacity($event);

        return view('public.event', [
            'event' => $event,
            'categories' => $categoriesWithAvailability,
            'globalRemaining' => $globalRemaining,
            'utmParams' => [
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'utm_content' => $request->query('utm_content'),
            ],
        ]);
    }
}
