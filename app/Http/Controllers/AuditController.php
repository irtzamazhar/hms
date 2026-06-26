<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view audit logs');

        $audits = Audit::with('user')
            ->when($request->model, fn ($q, $m) => $q->where('auditable_type', $m))
            ->when($request->event, fn ($q, $e) => $q->where('event', $e))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->where('auditable_id', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Filter options, derived from what has actually been recorded.
        $models = Audit::query()->distinct()->orderBy('auditable_type')->pluck('auditable_type');
        $events = Audit::query()->distinct()->orderBy('event')->pluck('event');
        $users = User::whereIn('id', Audit::whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('audit-logs.index', compact('audits', 'models', 'events', 'users'));
    }
}
