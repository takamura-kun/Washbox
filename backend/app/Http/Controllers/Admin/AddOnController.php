<?php

namespace App\Http\Controllers\Admin;

use App\Models\AddOn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AddOnController extends Controller
{
    /**
     * Display a listing of the add-ons.
     */
    public function index()
    {
        $addons = AddOn::latest()->get();
        return view('admin.services.index', compact('addons'));
    }

    /**
     * Show the form for creating a new add-on.
     */
    public function create()
    {
        return view('admin.addons.create');
    }

    /**
     * Store a newly created add-on
     */
    public function store(Request $request)
    {
        Log::info('AddOn Store Request:', $request->all());

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:add_ons,slug',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);
            
            $validated['is_active'] = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->storeAs(
                    'addons',
                    time() . '_' . $request->file('image')->getClientOriginalName(),
                    'public'
                );
                $imagePath = basename($imagePath);
            }

            $addon = AddOn::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'is_active' => $validated['is_active'],
                'image' => $imagePath,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Add-on created successfully!',
                    'addon' => $addon
                ]);
            }

            return redirect()->route('admin.services.index')->with('success', 'Add-on created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('AddOn Validation Error:', $e->errors());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('AddOn Store Error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating add-on: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error creating add-on: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified add-on.
     */
    public function show(AddOn $addon)
    {
        return view('admin.addons.show', compact('addon'));
    }

    /**
     * Show the form for editing the specified add-on.
     */
    public function edit(AddOn $addon)
    {
        return view('admin.addons.edit', compact('addon'));
    }

    /**
     * Update the specified add-on
     */
    public function update(Request $request, AddOn $addon)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:add_ons,slug,' . $addon->id,
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);
            
            $validated['is_active'] = filter_var($request->input('is_active', $addon->is_active), FILTER_VALIDATE_BOOLEAN);

            if ($request->hasFile('image')) {
                if ($addon->image) {
                    \Storage::disk('public')->delete('addons/' . $addon->image);
                }
                $imagePath = $request->file('image')->storeAs(
                    'addons',
                    time() . '_' . $request->file('image')->getClientOriginalName(),
                    'public'
                );
                $validated['image'] = basename($imagePath);
            }

            $addon->update($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Add-on updated successfully!',
                    'addon' => $addon
                ]);
            }

            return redirect()->route('admin.services.index')->with('success', 'Add-on updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('AddOn Update Validation Error:', $e->errors());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('AddOn Update Error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating add-on: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error updating add-on: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified add-on
     */
    public function destroy(AddOn $addon)
    {
        try {
            // Check if add-on is used in any laundries
            if ($addon->laundries()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete add-on that is used in existing laundries.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Cannot delete add-on that is used in existing laundries.');
            }

            if ($addon->image) {
                \Storage::disk('public')->delete('addons/' . $addon->image);
            }

            $addon->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Add-on deleted successfully!'
                ]);
            }

            return redirect()->route('admin.services.index')->with('success', 'Add-on deleted successfully!');

        } catch (\Exception $e) {
            Log::error('AddOn Delete Error: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting add-on: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error deleting add-on: ' . $e->getMessage());
        }
    }

    /**
     * Toggle add-on status
     */
    public function toggleStatus(Request $request, AddOn $addon)
    {
        try {
            $validated = $request->validate([
                'is_active' => 'required|boolean'
            ]);

            $addon->update(['is_active' => $validated['is_active']]);

            return response()->json([
                'success' => true,
                'message' => 'Add-on status updated!',
                'is_active' => $addon->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('AddOn Toggle Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }
}