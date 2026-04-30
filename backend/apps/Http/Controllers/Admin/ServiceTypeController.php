<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceType;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::whereIn('category', ['drop_off', 'self_service'])
            ->orderBy('category')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('admin.service-types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('admin.service-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:service_types,slug',
            'category' => 'required|in:drop_off,self_service',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'defaults.price' => 'nullable|numeric|min:0',
            'defaults.max_weight' => 'nullable|numeric|min:0',
            'defaults.turnaround' => 'nullable|integer|min:0',
            'defaults.pricing_type' => 'nullable|in:per_load,per_piece',
        ]);

        $defaults = [
            'price' => $request->input('defaults.price'),
            'max_weight' => $request->input('defaults.max_weight'),
            'turnaround' => $request->input('defaults.turnaround'),
            'pricing_type' => $request->input('defaults.pricing_type', 'per_load'),
        ];

        ServiceType::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'category' => $request->category,
            'description' => $request->description,
            'defaults' => $defaults,
            'icon' => $request->icon ?? 'bi-box-seam',
            'display_order' => $request->display_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Service type created successfully!');
    }

    public function edit(ServiceType $serviceType)
    {
        return view('admin.service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:service_types,slug,' . $serviceType->id,
            'category' => 'required|in:drop_off,self_service',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'defaults.price' => 'nullable|numeric|min:0',
            'defaults.max_weight' => 'nullable|numeric|min:0',
            'defaults.turnaround' => 'nullable|integer|min:0',
            'defaults.pricing_type' => 'nullable|in:per_load,per_piece',
        ]);

        $defaults = [
            'price' => $request->input('defaults.price'),
            'max_weight' => $request->input('defaults.max_weight'),
            'turnaround' => $request->input('defaults.turnaround'),
            'pricing_type' => $request->input('defaults.pricing_type', 'per_load'),
        ];

        $serviceType->update([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'category' => $request->category,
            'description' => $request->description,
            'defaults' => $defaults,
            'icon' => $request->icon ?? 'bi-box-seam',
            'display_order' => $request->display_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Service type updated successfully!');
    }

    public function destroy(ServiceType $serviceType)
    {
        $servicesCount = $serviceType->services()->count();

        if ($servicesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete service type because it's used by {$servicesCount} service(s).",
            ], 422);
        }

        DeletedRecord::snapshot($serviceType, 'service_type');
        ActivityLog::log('deleted', "Service type \"{$serviceType->name}\" deleted", 'service', null, [
            'name'     => $serviceType->name,
            'category' => $serviceType->category,
        ]);

        $serviceType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service type deleted successfully!',
        ]);
    }

    public function toggleStatus(ServiceType $serviceType)
    {
        $serviceType->is_active = !$serviceType->is_active;
        $serviceType->save();

        return response()->json([
            'success' => true,
            'message' => 'Service type status updated successfully!',
            'is_active' => $serviceType->is_active,
        ]);
    }
}
