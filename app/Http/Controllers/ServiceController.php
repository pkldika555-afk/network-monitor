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
        return view('services.index', compact('categories', 'services'));
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
        $service = Services::create($validated);
        return redirect()->route('services.index')->with('success', 'Services berhasil ditambahkan');
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
        $service->update($validated);
        return redirect()->route('services.index')->with('success', 'Services berhasil diupdate');
    }
    public function destroy(Services $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Services berhasil dihapus');
    }
    public function assign(Request $request, Services $service)
{
    $request->validate([
        'assigned_to' => 'nullable|string|max:150',
    ]);

    $service->update([
        'assigned_to' => $request->assigned_to ?: null,
        'assigned_at' => $request->assigned_to ? now() : null,
    ]);

    return response()->json([
        'id'          => $service->id,
        'assigned_to' => $service->assigned_to,
        'assigned_at' => $service->assigned_at?->format('d M Y H:i'),
    ]);
}
}
