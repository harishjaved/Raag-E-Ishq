<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSetting;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Request $request, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $event = Event::with(['categories' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order');
        }])->where('slug', 'sagar-kinare-2026')->firstOrFail();

        $snapshot = $inventoryService->getEventInventorySnapshot($event);

        $categoriesWithAvailability = $event->categories->map(function ($category) use ($snapshot) {
            $available = (int) ($snapshot['per_category'][$category->id]['available'] ?? 0);
            $category->available = $available;
            $category->low_availability = $available > 0 && $available < 20;
            $category->is_sold_out = $available <= 0;
            return $category;
        });

        $holdMinutes = max(1, (int) EventSetting::get('hold_duration_minutes', 30));

        return view('public.event', [
            'event' => $event,
            'categories' => $categoriesWithAvailability,
            'holdMinutes' => $holdMinutes,
            'utmParams' => [
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'utm_content' => $request->query('utm_content'),
            ],
        ]);
    }

    public function calendarIcs(): Response
    {
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Joygivers//Raag-E-Ishq//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:raag-e-ishq-20270117@joygivers.org',
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:20270117T133000Z',
            'DTEND:20270117T163000Z',
            'SUMMARY:Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers',
            'LOCATION:Chavara Cultural Centre (Chavara Hall), Near South Railway Station, Kochi, Kerala',
            'DESCRIPTION:An evening celebrating timeless Hindi melodies with a live orchestra, in support of girls\' education by Joygivers. Gates open 6:15 PM IST; programme starts 7:00 PM IST.',
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Raag-E-Ishq-Joygivers.ics"',
        ]);
    }
}
