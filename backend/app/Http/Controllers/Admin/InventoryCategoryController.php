<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::withCount('items')
                                       ->with(['items.centralStock'])
                                       ->get();
        return view('admin.inventory.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:inventory_categories,name',
            'color' => 'required|string|max:7',
        ]);
        
        InventoryCategory::create([
            'name' => $request->name,
            'color' => $request->color,
            'is_active' => true,
        ]);
        
        return redirect()->route('admin.inventory.manage')->with('success', 'Category created successfully!');
    }

    public function update(Request $request, InventoryCategory $category)
    {
        $request->validate([
            'name'  => 'required|string|max:100|unique:inventory_categories,name,'.$category->id,
            'color' => 'required|string|max:7',
        ]);
        
        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);
        
        return redirect()->route('admin.inventory.manage')->with('success', 'Category updated successfully!');
    }

    public function destroy(InventoryCategory $category)
    {
        if ($category->items()->count() > 0) {
            $category->items()->delete();
        }
        $category->delete();
        
        return redirect()->route('admin.inventory.manage')->with('success', 'Category deleted successfully.');
    }

    public function getActive()
    {
        return response()->json(InventoryCategory::where('is_active', true)->get());
    }
}
