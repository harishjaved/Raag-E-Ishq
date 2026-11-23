<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $query = AuditLog::with('user')->latest('id');

        if ($action = $request->query('action')) {
            if ($action === 'EXPIRATIONS') {
                $query->whereIn('action', ['BOOKING_HOLD_EXPIRED', 'PAYMENT_EXPIRED']);
            } else {
                $query->where('action', $action);
            }
        }

        $logs = $query->paginate(30);

        return view('admin.audit_logs.index', [
            'logs' => $logs,
            'currentAction' => $action,
        ]);
    }
}
