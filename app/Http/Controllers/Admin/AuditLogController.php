<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest('id');

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(30);

        return view('admin.audit_logs.index', [
            'logs' => $logs,
            'currentAction' => $action,
        ]);
    }
}
