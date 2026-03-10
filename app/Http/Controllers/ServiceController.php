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

        return view ('services.index', compact('categories', 'services'));
    }
}
