<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('module', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity-logs.index', compact('logs', 'users', 'actions'));
    }
}
