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
            ->when($request->day, fn($q) => $q->whereDay('checked_at', $request->day))
            ->when($request->month, fn($q) => $q->whereMonth('checked_at', $request->month))
            ->when($request->year, fn($q) => $q->whereYear('checked_at', $request->year))

            ->latest('checked_at')
            ->paginate(50)
            ->withQueryString();

        $categories = \App\Models\Services::selectRaw('category, count(*) as total, SUM(status = "offline") as offline_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $services = \App\Models\Services::orderBy('name')->get();
        $lastreset = CheckLog::min('checked_at');


        return view('logs.index', [
            'logs' => $logs,
            'services' => $services,
            'lastReset' => cache('logs_last_reset'),
        ]);
    }
    public function resetLogs()
    {
        CheckLog::truncate();
        cache(['logs_last_reset' => now()], now()->addDays(30));
        return back()->with('success', 'Semua riwayat log telah dibersihkan!');
    }
 
}