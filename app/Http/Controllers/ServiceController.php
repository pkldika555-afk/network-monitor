<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Services::latest()->get();
        $categories = Services::selectRaw('category, count(*) as total, SUM(status = "offline") as offline_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        $catList = Services::distinct()->pluck('category')->filter()->sort()->values();
        $depList = Services::distinct()->pluck('department')->filter()->sort()->values();
        return view('services.index', compact('categories', 'services', 'catList', 'depList'));
    }
    public function store(Request $request)
    {
        $url = $request->url;
        if (!str_starts_with($url, 'http')) {
            $url = 'http://' . $url;
        }
        $request->merge(['url' => $url]);
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|url',
            'category' => 'required|string|max:100',
            'department' => 'required|string|max:150',
            'auth_type' => 'in:none,bearer,basic',
            'auth_value' => 'nullable|string',
        ]);
        try {
            $service = Services::create($validated);
            return redirect()->route('services.index')->with('success', 'Services berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan service: ' . $e->getMessage());
        }
    }
    public function update(Request $request, Services $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|url',
            'category' => 'required|string|max:100',
            'department' => 'required|string|max:150',
            'auth_type' => 'in:none,bearer,basic',
        ]);
        try {
            $service->update($validated);
            return redirect()->route('services.index')->with('success', 'Services berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate service: ' . $e->getMessage());
        }
    }
    public function destroy(Services $service)
    {
        try {
            $service->delete();
            return redirect()->route('services.index')->with('success', 'Services berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('services.index')->with('error', 'Gagal menghapus service: ' . $e->getMessage());
        }
    }
    public function assign(Request $request, Services $service)
    {
        $request->validate([
            'assigned_to' => 'nullable|string|max:150',
        ]);

        try {
            $service->update([
                'assigned_to' => $request->assigned_to ?: null,
                'assigned_at' => $request->assigned_to ? now() : null,
            ]);

            return response()->json([
                'id' => $service->id,
                'assigned_to' => $service->assigned_to,
                'assigned_at' => $service->assigned_at?->format('d M Y H:i'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal assign service: ' . $e->getMessage()], 500);
        }
    }
public function stream()
{
    return response()->stream(function () {
        while (true) {
            $data = json_encode([
                'count'      => Services::count(),
                'updated_at' => Services::max('updated_at'),
            ]);
            echo "data: {$data}\n\n";
            ob_flush();
            flush();
            sleep(1);
        }
    }, 200, [
        'Content-Type'      => 'text/event-stream',
        'Cache-Control'     => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
}
