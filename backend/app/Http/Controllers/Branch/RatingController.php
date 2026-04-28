<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\CustomerRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Display branch-specific customer ratings
     */
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        
        if (!$branch) {
            return redirect()->route('branch.dashboard')
                ->with('error', 'Authentication required.');
        }

        // Mark all ratings for this branch as viewed IMMEDIATELY when accessing this page
        CustomerRating::where('branch_id', $branch->id)
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        $query = CustomerRating::with(['customer', 'branch'])
            ->where('branch_id', $branch->id);

        // Apply filters
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Group ratings by customer
        $allRatings = $query->latest()->get();
        $customersWithRatings = $allRatings->groupBy('customer_id')->map(function($customerRatings) {
            $customer = $customerRatings->first()->customer;
            return (object) [
                'customer' => $customer,
                'average_rating' => round($customerRatings->avg('rating'), 1),
                'rating_count' => $customerRatings->count(),
                'latest_rating' => $customerRatings->first(),
                'all_ratings' => $customerRatings
            ];
        })->values();

        // Calculate branch rating statistics
        $stats = [
            'total_ratings' => CustomerRating::where('branch_id', $branch->id)->count(),
            'average_rating' => CustomerRating::where('branch_id', $branch->id)->avg('rating') ?? 0,
            'five_star' => CustomerRating::where('branch_id', $branch->id)->where('rating', 5)->count(),
            'four_star' => CustomerRating::where('branch_id', $branch->id)->where('rating', 4)->count(),
            'three_star' => CustomerRating::where('branch_id', $branch->id)->where('rating', 3)->count(),
            'two_star' => CustomerRating::where('branch_id', $branch->id)->where('rating', 2)->count(),
            'one_star' => CustomerRating::where('branch_id', $branch->id)->where('rating', 1)->count(),
        ];

        return view('branch.ratings.index', compact('customersWithRatings', 'stats'));
    }

    /**
     * Show specific rating details
     */
    public function show(CustomerRating $rating)
    {
        $branch = Auth::guard('branch')->user();
        
        if (!$branch) {
            return redirect()->route('branch.dashboard')
                ->with('error', 'Authentication required.');
        }

        // Ensure rating belongs to branch
        if ($rating->branch_id !== $branch->id) {
            abort(403, 'Unauthorized: This rating belongs to a different branch.');
        }

        $rating->load(['customer', 'branch']);
        
        return view('branch.ratings.show', compact('rating'));
    }
}