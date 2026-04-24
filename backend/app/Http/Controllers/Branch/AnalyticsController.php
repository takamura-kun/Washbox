<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\RetailSale;
use App\Models\Expense;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->guard('branch')->user()->id;

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        // KPI Statistics
        $totalRevenue = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $retailSales = RetailSale::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalExpenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $stats = [
            'total_laundries'  => Laundry::where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'total_revenue'    => $totalRevenue + $retailSales,
            'laundry_revenue'  => $totalRevenue,
            'total_customers'  => Laundry::where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct('customer_id')
                ->count('customer_id'),
            'retail_sales'     => $retailSales,
            'total_expenses'   => $totalExpenses,
            'total_profit'     => ($totalRevenue + $retailSales) - $totalExpenses,
        ];

        // Build full date range (last 7 days of the selected range)
        $rangeEnd   = $endDate->copy()->startOfDay();
        $rangeStart = $rangeEnd->copy()->subDays(6);
        $allDates = collect();
        for ($d = $rangeStart->copy(); $d->lte($rangeEnd); $d->addDay()) {
            $allDates->put($d->format('Y-m-d'), 0);
        }

        // Revenue Trend — fill missing days with 0
        $revenueTrendRaw = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$rangeStart->startOfDay(), $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        $revenueTrend = $allDates->merge($revenueTrendRaw);

        // Top Services — daily counts per service for line chart (last 7 days)
        $topServiceNames = Laundry::from('laundries')
            ->where('laundries.branch_id', $branchId)
            ->whereBetween('laundries.created_at', [$rangeStart->startOfDay(), $endDate])
            ->whereNotNull('laundries.service_id')
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->select('services.id', 'services.name as service_name', DB::raw('COUNT(*) as total'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('service_name', 'services.id');

        $servicesDailyRaw = Laundry::from('laundries')
            ->where('laundries.branch_id', $branchId)
            ->whereBetween('laundries.created_at', [$rangeStart->startOfDay(), $endDate])
            ->whereIn('laundries.service_id', $topServiceNames->keys())
            ->join('services', 'laundries.service_id', '=', 'services.id')
            ->selectRaw('DATE(laundries.created_at) as date, laundries.service_id, COUNT(*) as count')
            ->groupBy('date', 'laundries.service_id')
            ->get()
            ->groupBy('service_id');

        $serviceColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
        $topServicesDatasets = $topServiceNames->values()->map(function ($name, $idx) use ($servicesDailyRaw, $topServiceNames, $allDates, $serviceColors) {
            $serviceId = $topServiceNames->search($name);
            $raw = $servicesDailyRaw->get($serviceId, collect())->pluck('count', 'date');
            $data = $allDates->merge($raw)->values()->map(fn($v) => (int) $v)->toArray();
            return [
                'label'           => $name,
                'data'            => $data,
                'borderColor'     => $serviceColors[$idx % count($serviceColors)],
                'backgroundColor' => $serviceColors[$idx % count($serviceColors)] . '20',
                'tension'         => 0.4,
                'fill'            => false,
                'borderWidth'     => 2,
                'pointRadius'     => 4,
            ];
        })->values()->toArray();

        // Order Status Distribution
        $orderStatus = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Peak Hours Analysis
        $peakHours = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Customer Growth Trend — fill missing days with 0
        $customerGrowthRaw = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$rangeStart->startOfDay(), $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT customer_id) as customers')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('customers', 'date');

        $customerGrowth = $allDates->merge($customerGrowthRaw);

        // Top Customers
        $topCustomerRows = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        $customerIds = $topCustomerRows->pluck('customer_id');
        $customers = Customer::whereIn('id', $customerIds)->pluck('name', 'id');
        $topCustomers = $topCustomerRows->map(function ($row) use ($customers) {
            $row->customer_name = $customers[$row->customer_id] ?? 'Unknown';
            return $row;
        });

        // Expenses Trend — fill missing days with 0
        $expensesTrendRaw = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$rangeStart->startOfDay(), $endDate])
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $expensesTrend = $allDates->merge($expensesTrendRaw);

        // Payment Methods Distribution (only paid orders)
        $paymentMethods = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('payment_method')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $chartData = [
            'revenue_trend' => [
                'labels' => $revenueTrend->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'data'   => $revenueTrend->values()->map(fn($v) => (float) $v)->toArray(),
            ],
            'top_services' => [
                'labels'   => $allDates->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'datasets' => $topServicesDatasets,
            ],
            'order_status' => [
                'labels' => $orderStatus->pluck('status')->map(fn($s) => ucfirst($s))->toArray(),
                'data'   => $orderStatus->pluck('count')->toArray(),
            ],
            'peak_hours' => [
                'labels' => $peakHours->pluck('hour')->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')->toArray(),
                'data'   => $peakHours->pluck('count')->toArray(),
            ],
            'customer_growth' => [
                'labels' => $customerGrowth->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'data'   => $customerGrowth->values()->map(fn($v) => (int) $v)->toArray(),
            ],
            'profit_trend' => [
                'labels' => $expensesTrend->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'data'   => $allDates->keys()->map(function ($date) use ($revenueTrend, $expensesTrend) {
                    $rev = (float) ($revenueTrend->get($date, 0));
                    $exp = (float) ($expensesTrend->get($date, 0));
                    return round($rev - $exp, 2);
                })->toArray(),
            ],
            'expenses_trend' => [
                'labels' => $expensesTrend->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
                'data'   => $expensesTrend->values()->map(fn($v) => (float) $v)->toArray(),
            ],
            'payment_methods' => [
                'labels' => $paymentMethods->pluck('payment_method')->map(fn($m) => ucfirst($m))->toArray(),
                'data'   => $paymentMethods->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            ],
        ];

        // Customer Satisfaction
        $ratingsRaw = \App\Models\CustomerRating::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating');

        $totalRatings   = $ratingsRaw->sum();
        $avgRating      = $totalRatings > 0
            ? round($ratingsRaw->reduce(fn($carry, $count, $star) => $carry + ($star * $count), 0) / $totalRatings, 1)
            : 0;

        $ratingCounts = collect([1,2,3,4,5])->mapWithKeys(fn($s) => [$s => (int)($ratingsRaw[$s] ?? 0)]);

        $recentReviews = \App\Models\CustomerRating::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('comment')
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id','customer_id','rating','comment','created_at']);

        // Rating trend (daily avg)
        $ratingTrendRaw = \App\Models\CustomerRating::where('branch_id', $branchId)
            ->whereBetween('created_at', [$rangeStart->startOfDay(), $endDate])
            ->selectRaw('DATE(created_at) as date, ROUND(AVG(rating),1) as avg_rating')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('avg_rating', 'date');

        $ratingTrend = $allDates->map(fn($_, $date) => (float)($ratingTrendRaw[$date] ?? 0));

        $chartData['rating_trend'] = [
            'labels' => $ratingTrend->keys()->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'data'   => $ratingTrend->values()->toArray(),
        ];

        return view('branch.analytics.index', compact(
            'stats', 'chartData', 'topCustomers', 'startDate', 'endDate',
            'avgRating', 'totalRatings', 'ratingCounts', 'recentReviews'
        ));
    }

    public function export(Request $request)
    {
        $branchId = auth()->guard('branch')->user()->id;

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $laundries = Laundry::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('customer:id,name', 'service:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'analytics-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($laundries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tracking #', 'Customer', 'Service', 'Status', 'Payment Method', 'Total Amount', 'Date']);
            foreach ($laundries as $l) {
                fputcsv($handle, [
                    $l->tracking_number,
                    $l->customer->name ?? 'N/A',
                    $l->service->name ?? 'N/A',
                    $l->status,
                    $l->payment_method ?? 'N/A',
                    $l->total_amount,
                    $l->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
