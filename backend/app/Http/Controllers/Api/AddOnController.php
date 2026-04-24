<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    /**
     * Return active add-ons for the mobile app.
     *
     * GET /api/v1/addons
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AddOn::where('is_active', true);

            // Optional search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $addons = $query
                ->orderBy('name')
                ->get()
                ->map(function ($addon) {
                    return [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'slug' => $addon->slug,
                        'description' => $addon->description,
                        'price' => $addon->price,
                        'is_active' => $addon->is_active,
                        'image' => $addon->image,
                        'image_url' => $addon->image_url,
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $addons,
                'total'   => $addons->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load add-ons.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return a single add-on.
     *
     * GET /api/v1/addons/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $addon = AddOn::where('is_active', true)->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'slug' => $addon->slug,
                    'description' => $addon->description,
                    'price' => $addon->price,
                    'is_active' => $addon->is_active,
                    'image' => $addon->image,
                    'image_url' => $addon->image_url,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Add-on not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load add-on.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
