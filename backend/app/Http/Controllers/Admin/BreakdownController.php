<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class BreakdownController extends Controller
{
    /**
     * Revenue Breakdown - Detailed breakdown of today's revenue
     */
    public function revenueBreakdown()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        // By Payment Method
        $byPaymentMethod = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(total_amount) as total, COUNT(*) as count')
            ->get();

        // By Service Type
        $byServiceType = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->where('status', 'completed')
            ->with('service')
            ->groupBy('service_id')
            ->selectRaw('service_id, SUM(total_amount) as total, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                $item->service_name = $item->service->name ?? 'Unknown';
                return $item;
            });

        // By Branch
        $byBranch = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->where('status', 'completed')
            ->with('branch')
            ->groupBy('branch_id')
            ->selectRaw('branch_id, SUM(total_amount) as total, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                $item->branch_name = $item->branch->name ?? 'Unknown';
                return $item;
            });

        // By Payment Status
        $byPaymentStatus = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->groupBy('payment_status')
            ->selectRaw('payment_status, SUM(total_amount) as total, COUNT(*) as count')
            ->get();

        // Calculate totals
        $totalRevenue = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->where('status', 'completed')
            ->sum('total_amount');

        return view('admin.breakdowns.revenue', [
            'byPaymentMethod' => $byPaymentMethod,
            'byServiceType' => $byServiceType,
            'byBranch' => $byBranch,
            'byPaymentStatus' => $byPaymentStatus,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    /**
     * Laundries Breakdown - Detailed breakdown of today's laundries
     */
    public function laundries()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        // By Status
        $byStatus = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get();

        // By Service Type
        $byServiceType = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->with('service')
            ->groupBy('service_id')
            ->selectRaw('service_id, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                $item->service_name = $item->service->name ?? 'Unknown';
                return $item;
            });

        // By Branch
        $byBranch = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->with('branch')
            ->groupBy('branch_id')
            ->selectRaw('branch_id, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                $item->branch_name = $item->branch->name ?? 'Unknown';
                return $item;
            });

        // By Payment Status
        $byPaymentStatus = Laundry::whereBetween('created_at', [$today, $tomorrow])
            ->groupBy('payment_status')
            ->selectRaw('payment_status, COUNT(*) as count')
            ->get();

        // Total count
        $totalLaundries = Laundry::whereBetween('created_at', [$today, $tomorrow])->count();

        return view('admin.breakdowns.laundries', [
            'byStatus' => $byStatus,
            'byServiceType' => $byServiceType,
            'byBranch' => $byBranch,
            'byPaymentStatus' => $byPaymentStatus,
            'totalLaundries' => $totalLaundries,
        ]);
    }

    /**
     * Customers Breakdown - Detailed breakdown of active customers
     */
    public function customers()
    {
        // By Registration Type
        $byRegistrationType = Customer::where('is_active', true)
            ->groupBy('registration_type')
            ->selectRaw('registration_type, COUNT(*) as count')
            ->get();

        // By Branch
        $byBranch = Customer::where('is_active', true)
            ->with('branch')
            ->groupBy('branch_id')
            ->selectRaw('branch_id, COUNT(*) as count')
            ->get()
            ->map(function ($item) {
                $item->branch_name = $item->branch->name ?? 'Unknown';
                return $item;
            });

        // By Activity Level (orders in last 30 days)
        $byActivityLevel = Customer::where('is_active', true)
            ->withCount(['laundries' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->get()
            ->groupBy(function ($customer) {
                $count = $customer->laundries_count;
                if ($count >= 5) return 'Very Active (5+ orders)';
                if ($count >= 2) return 'Active (2-4 orders)';
                if ($count >= 1) return 'Moderate (1 order)';
                return 'Inactive (0 orders)';
            })
            ->map(function ($group, $key) {
                return (object) [
                    'activity_level' => $key,
                    'count' => count($group),
                ];
            });

        // By Customer Rating
        $byRating = Customer::where('is_active', true)
            ->with('ratings')
            ->get()
            ->groupBy(function ($customer) {
                $avgRating = $customer->ratings->avg('rating') ?? 0;
                return (int) round($avgRating);
            })
            ->map(function ($group, $key) {
                return (object) [
                    'rating' => $key,
                    'count' => count($group),
                ];
            })
            ->sortByDesc('rating');

        // Top 10 Customers by spending
        $topCustomers = Customer::where('is_active', true)
            ->with('laundries')
            ->get()
            ->map(function ($customer) {
                $customer->total_spent = $customer->laundries->sum('total_amount');
                return $customer;
            })
            ->sortByDesc('total_spent')
            ->take(10);

        // Total active customers
        $totalActiveCustomers = Customer::where('is_active', true)->count();

        return view('admin.breakdowns.customers', [
            'byRegistrationType' => $byRegistrationType,
            'byBranch' => $byBranch,
            'byActivityLevel' => $byActivityLevel,
            'byRating' => $byRating,
            'topCustomers' => $topCustomers,
            'totalActiveCustomers' => $totalActiveCustomers,
        ]);
    }
}
