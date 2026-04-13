<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuditLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_audit|full_access'),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user:id,name')
            ->when($request->entity_type, fn($q, $t) => $q->where('entity_type', $t))
            ->when($request->entity_id, fn($q, $id) => $q->where('entity_id', $id))
            ->when($request->action, fn($q, $a) => $q->where('action', 'like', "%{$a}%"))
            ->when($request->user_id, fn($q, $uid) => $q->where('user_id', $uid))
            ->orderByDesc('occurred_at')
            ->paginate(30);

        return response()->json($logs);
    }
}
