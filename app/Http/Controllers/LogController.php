<?php

namespace App\Http\Controllers;

use App\Models\CheckLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = CheckLog::with('service')
            ->when($request->service_id, fn($q) => $q->where('service_id', $request->service_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('checked_at')
            ->paginate(50)
            ->withQueryString();
        $categories = \App\Models\Services::selectRaw('category, count(*) as total, SUM(status = "offline") as offline_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        $services = \App\Models\Services::orderBy('name')->get();

        return view('logs.index', compact('logs', 'services', 'categories'));
    }
}