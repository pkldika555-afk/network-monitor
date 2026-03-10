<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->get();
        $categories = Service::distinct()->pluck('category')->sort()->value();

        return view('services.index', compact('categories', 'services'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|url',
            'category' => 'required|string|max:100',
            'departemen' => 'required|string|max:150',
            'auth_type' => 'in:none,bearer,basic',
            'auth_value' => 'nullable|string',
        ]);
        $service = Service::create($validated);
        return redirect()->route('services.index')->with('success', 'Services berhasil ditambahkan');
    }
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|url',
            'category' => 'required|string|max:100',
            'departemen' => 'required|string|max:150',
            'auth_type' => 'in:none,bearer,basic',
        ]);
        $service->update($validated);
        return redirect()->route('services.index')->with('success', 'Services berhasil diupdate');
    }
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Services berhasil dihapus');
    }
}
