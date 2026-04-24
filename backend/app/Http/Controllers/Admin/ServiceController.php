<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    // List all services
    public function index()
    {
        $services = Service::withCount('laundries')->latest()->get();
        return view('admin.services.index', compact('services'));
    }

    // Show form to create a new service
    public function create()
    {
        $services = Service::all();
        return view('admin.services.create', compact('services'));
    }

    // Store a new service
    public function store(Request $request)
    {
        try {
            $request->merge(['is_active' => $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true]);

            $rules = [
                'name'            => 'required|string|max:255',
                'description'     => 'nullable|string',
                'min_weight'      => 'nullable|numeric|min:0',
                'max_weight'      => 'nullable|numeric|min:0|max:100',
                'service_type'    => 'nullable|string|max:255',
                'service_type_id' => 'nullable|exists:service_types,id',
                'category'        => 'required|string|in:drop_off,self_service,addon',
                'pricing_type'    => 'required|in:per_piece,per_load',
                'turnaround_time' => 'required|integer|min:0|max:168',
                'slug'            => 'nullable|string|unique:services,slug',
                'is_active'       => 'boolean',
                'icon'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];

            if ($request->pricing_type === 'per_piece') {
                $rules['price_per_piece'] = 'required|numeric|min:0';
                $rules['price_per_load']  = 'nullable|numeric|min:0';
            } else {
                $rules['price_per_load']  = 'required|numeric|min:0';
                $rules['price_per_piece'] = 'nullable|numeric|min:0';
            }

            $messages = [
                'max_weight.max' => 'Maximum kg exceeded the max limit.',
            ];

            $request->validate($rules, $messages);

            $iconPath = null;
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('service-icons', 'public');
            }

            $pricePerPiece = null;
            $pricePerLoad  = null;

            if ($request->pricing_type === 'per_piece') {
                $pricePerPiece = $request->price_per_piece;
            } else {
                $pricePerLoad = $request->price_per_load;
            }

            $serviceTypeText = $request->service_type
                ?? optional(ServiceType::find($request->service_type_id))->name
                ?? $request->name;

            $service = Service::create([
                'name'                        => $request->name,
                'description'                 => $request->description,
                'price_per_piece'             => $pricePerPiece,
                'price_per_load'              => $pricePerLoad,
                'min_weight'                  => $request->min_weight,
                'max_weight'                  => $request->max_weight,
                'allow_excess_weight'         => $request->boolean('allow_excess_weight'),
                'excess_weight_charge_per_kg' => $request->boolean('allow_excess_weight') ? $request->excess_weight_charge_per_kg : null,
                'service_type'                => $serviceTypeText,
                'service_type_id'             => $request->service_type_id,
                'pricing_type'                => $request->pricing_type,
                'turnaround_time'             => $request->turnaround_time,
                'slug'                        => $request->slug ?? Str::slug($request->name),
                'icon_path'                   => $iconPath,
                'is_active'                   => $request->is_active ?? true,
                'category'                    => $request->category,
            ]);

            // Attach supplies if provided
            if ($request->has('supplies') && is_string($request->supplies)) {
                $supplies = json_decode($request->supplies, true);
                if (is_array($supplies)) {
                    $pivotData = [];
                    foreach ($supplies as $supply) {
                        if (isset($supply['supply_id']) && isset($supply['quantity_required'])) {
                            $pivotData[$supply['supply_id']] = [
                                'quantity_required' => $supply['quantity_required']
                            ];
                        }
                    }
                    if (!empty($pivotData)) {
                        $service->supplies()->attach($pivotData);
                    }
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service created successfully!',
                    'service' => $service,
                ]);
            }

            return redirect()->route('admin.services.index')
                ->with('success', 'Service created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating service: ' . $e->getMessage(),
                ], 500);
            }
            throw $e;
        }
    }

    // Show a single service
    public function show(Service $service)
    {
        $stats = [
            'total_laundries' => $service->laundries()->count(),
            'completed_laundries' => $service->laundries()->where('status', 'completed')->count(),
            'total_revenue' => $service->laundries()->where('status', 'completed')->sum('total_amount'),
            'total_weight' => $service->laundries()->sum('weight'),
            'avg_laundry_value' => $service->laundries()->where('status', 'completed')->avg('total_amount') ?? 0,
        ];

        $recent_laundries = $service->laundries()
            ->with(['customer', 'branch'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.services.show', compact('service', 'stats', 'recent_laundries'));
    }

    // =========================================================================
    // BUG FIX: $serviceTypes was never passed to the edit view,
    // so @foreach($serviceTypes ?? [] ...) always looped over nothing and the
    // Service Type <select> rendered completely empty — making it impossible
    // to change the service type on the edit page.
    // =========================================================================
    public function edit(Service $service)
    {
        $services = Service::all();

        $serviceTypes = ServiceType::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('admin.services.edit', compact('service', 'services', 'serviceTypes'));
    }

    // Update a service
    public function update(Request $request, Service $service)
    {
        $request->merge(['is_active' => $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true]);

        $rules = [
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'min_weight'      => 'nullable|numeric|min:0',
            'max_weight'      => 'nullable|numeric|min:0|max:100',
            'service_type'    => 'nullable|string|max:255',
            'service_type_id' => 'nullable|exists:service_types,id',
            'category'        => 'required|string|in:drop_off,self_service,addon',
            'pricing_type'    => 'required|in:per_piece,per_load',
            'turnaround_time' => 'required|integer|min:0|max:168',
            'slug'            => 'nullable|string|unique:services,slug,' . $service->id,
            'is_active'       => 'boolean',
            'icon'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if ($request->pricing_type === 'per_piece') {
            $rules['price_per_piece'] = 'required|numeric|min:0';
            $rules['price_per_load']  = 'nullable|numeric|min:0';
        } else {
            $rules['price_per_load']  = 'required|numeric|min:0';
            $rules['price_per_piece'] = 'nullable|numeric|min:0';
        }

        $messages = [
            'max_weight.max' => 'Maximum kg exceeded the max limit.',
        ];

        $request->validate($rules, $messages);

        if ($request->hasFile('icon')) {
            if ($service->icon_path) {
                Storage::disk('public')->delete($service->icon_path);
            }
            $service->icon_path = $request->file('icon')->store('service-icons', 'public');
        }

        $pricePerPiece = null;
        $pricePerLoad  = null;

        if ($request->pricing_type === 'per_piece') {
            $pricePerPiece = $request->price_per_piece;
        } else {
            $pricePerLoad = $request->price_per_load;
        }

        $serviceTypeText = $request->service_type
            ?? optional(ServiceType::find($request->service_type_id))->name
            ?? $service->service_type
            ?? $request->name;

        $service->update([
            'name'                        => $request->name,
            'description'                 => $request->description,
            'price_per_piece'             => $pricePerPiece,
            'price_per_load'              => $pricePerLoad,
            'min_weight'                  => $request->min_weight,
            'max_weight'                  => $request->max_weight,
            'allow_excess_weight'         => $request->boolean('allow_excess_weight'),
            'excess_weight_charge_per_kg' => $request->boolean('allow_excess_weight') ? $request->excess_weight_charge_per_kg : null,
            'service_type'                => $serviceTypeText,
            'service_type_id'             => $request->service_type_id ?? $service->service_type_id,
            'pricing_type'                => $request->pricing_type,
            'turnaround_time'             => $request->turnaround_time,
            'slug'                        => $request->slug ?? Str::slug($request->name),
            'is_active'                   => $request->is_active ?? true,
            'category'                    => $request->category,
        ]);

        // Update supplies if provided
        if ($request->has('supplies') && is_string($request->supplies)) {
            $supplies = json_decode($request->supplies, true);
            if (is_array($supplies)) {
                // Detach all existing supplies
                $service->supplies()->detach();
                // Attach new supplies
                $pivotData = [];
                foreach ($supplies as $supply) {
                    if (isset($supply['supply_id']) && isset($supply['quantity_required'])) {
                        $pivotData[$supply['supply_id']] = [
                            'quantity_required' => $supply['quantity_required']
                        ];
                    }
                }
                if (!empty($pivotData)) {
                    $service->supplies()->attach($pivotData);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully!',
                'service' => $service,
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully!');
    }
    public function toggleStatus(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);

            $request->validate(['is_active' => 'required|boolean']);

            $service->is_active = $request->is_active;
            $service->save();

            return response()->json([
                'success'   => true,
                'message'   => 'Service status updated successfully!',
                'is_active' => $service->is_active,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Service not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating service status: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete a service
    public function destroy(Service $service)
    {
        try {
            $laundryCount = $service->laundries()->count();

            if ($laundryCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete service because it has been used in {$laundryCount} laundry order(s). You can deactivate it instead.",
                ], 422);
            }

            if ($service->icon_path) {
                Storage::disk('public')->delete($service->icon_path);
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting service: ' . $e->getMessage(),
            ], 500);
        }
    }
}
