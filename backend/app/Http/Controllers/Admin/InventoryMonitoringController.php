<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryMonitoringController extends Controller
{
    public function index()
    {
        return view('admin.inventory.monitoring.index');
    }

    public function dashboard()
    {
        return view('admin.inventory.monitoring.dashboard');
    }

    public function alerts()
    {
        return response()->json(['alerts' => []]);
    }

    public function lowStock()
    {
        return response()->json(['items' => []]);
    }

    public function expiringItems()
    {
        return response()->json(['items' => []]);
    }
}
