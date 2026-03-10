<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Services::latest()->get();
        $categories = Services::distinct()->pluck('category')->sort()->values();

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
}
