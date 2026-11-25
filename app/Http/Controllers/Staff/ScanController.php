<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Services\CheckinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(Request $request): View
    {
        $todayCheckinsCount = Checkin::whereDate('checked_in_at', now()->toDateString())->count();

        return view('staff.scan', [
            'todayCheckinsCount' => $todayCheckinsCount,
            'user' => $request->user(),
        ]);
    }

    public function verify(Request $request, CheckinService $checkinService): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => ['required', 'string'],
            'signature' => ['required', 'string'],
            'gate' => ['nullable', 'string', 'max:20'],
        ]);

        $result = $checkinService->checkinTicketByQr(
            uuid: $validated['uuid'],
            signature: $validated['signature'],
            gate: $validated['gate'] ?? 'GATE_1',
            staff: $request->user()
        );

        $payload = [
            'result' => $result['result'], // GREEN_VALID, ORANGE_ALREADY_USED, RED_INVALID
            'message' => $result['message'],
            'attendee_name' => $result['ticket']?->attendee_name,
            'category' => $result['ticket']?->category?->name,
            'ticket_number' => $result['ticket']?->ticket_number,
            'checked_in_at' => $result['checkin']?->checked_in_at?->format('H:i:s d M Y'),
        ];

        return response()->json($payload, $result['code'] ?? 200);
    }

    public function manualLookup(Request $request, CheckinService $checkinService): JsonResponse
    {
        $validated = $request->validate([
            'ticket_number' => ['required', 'string'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
            'gate' => ['nullable', 'string', 'max:20'],
        ]);

        $result = $checkinService->manualLookupAndCheckin(
            ticketNumber: $validated['ticket_number'],
            reason: $validated['reason'],
            gate: $validated['gate'] ?? 'GATE_1',
            staff: $request->user()
        );

        $payload = [
            'result' => $result['result'],
            'message' => $result['message'],
            'attendee_name' => $result['ticket']?->attendee_name,
            'category' => $result['ticket']?->category?->name,
            'ticket_number' => $result['ticket']?->ticket_number,
            'checked_in_at' => $result['checkin']?->checked_in_at?->format('H:i:s d M Y'),
        ];

        return response()->json($payload, $result['code'] ?? 200);
    }
}
