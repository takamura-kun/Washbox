<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\PickupRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function historical(Request $request)
    {
        $metric = $request->input('metric', 'hourly_pickups');
        $days = $request->input('days', 7);
        
        $startDate = Carbon::now()->subDays($days);
        
        $data = match($metric) {
            'daily_pickups' => $this->getDailyPickups($startDate),
            'hourly_pickups' => $this->getHourlyPickups($startDate),
            'daily_revenue' => $this->getDailyRevenue($startDate),
            default => []
        };
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    public function customerBehavior(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $data = [
            'repeat_customers' => $this->getRepeatCustomers($startDate),
            'avg_order_value' => $this->getAverageOrderValue($startDate),
            'churn_indicators' => $this->getChurnIndicators($startDate),
            'customer_segments' => $this->getCustomerSegments($startDate)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    public function realtime(Request $request)
    {
        $data = [
            'active_orders' => $this->getActiveOrders(),
            'pending_pickups' => $this->getPendingPickups(),
            'today_revenue' => $this->getTodayRevenue(),
            'today_orders' => $this->getTodayOrders(),
            'staff_utilization' => $this->getStaffUtilization(),
            'branch_performance' => $this->getBranchPerformance()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }
    
    private function getDailyPickups($startDate)
    {
        return PickupRequest::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'count' => $item->count
            ]);
    }
    
    private function getHourlyPickups($startDate)
    {
        return PickupRequest::where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($item) => [
                'hour' => $item->hour,
                'count' => $item->count
            ]);
    }
    
    private function getDailyRevenue($startDate)
    {
        return Laundry::where('created_at', '>=', $startDate)
            ->whereIn('status', ['paid', 'completed'])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'revenue' => (float) $item->revenue
            ]);
    }
    
    private function getRepeatCustomers($startDate)
    {
        return Laundry::where('created_at', '>=', $startDate)
            ->selectRaw('customer_id, COUNT(*) as order_count')
            ->groupBy('customer_id')
            ->having('order_count', '>', 1)
            ->count();
    }
    
    private function getAverageOrderValue($startDate)
    {
        return (float) Laundry::where('created_at', '>=', $startDate)
            ->whereIn('status', ['paid', 'completed'])
            ->avg('total_amount') ?? 0;
    }
    
    private function getChurnIndicators($startDate)
    {
        $inactiveCustomers = Laundry::selectRaw('customer_id, MAX(created_at) as last_order')
            ->groupBy('customer_id')
            ->having('last_order', '<', Carbon::now()->subDays(30))
            ->count();
            
        return [
            'inactive_customers' => $inactiveCustomers,
            'at_risk_customers' => $this->getAtRiskCustomers()
        ];
    }
    
    private function getCustomerSegments($startDate)
    {
        $segments = DB::table('laundries')
            ->select('customer_id')
            ->selectRaw('COUNT(*) as order_count, SUM(total_amount) as total_spent')
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['paid', 'completed'])
            ->groupBy('customer_id')
            ->get()
            ->groupBy(function ($customer) {
                if ($customer->total_spent > 5000) return 'high_value';
                if ($customer->total_spent > 2000) return 'medium_value';
                return 'low_value';
            });
            
        return [
            'high_value' => $segments->get('high_value', collect())->count(),
            'medium_value' => $segments->get('medium_value', collect())->count(),
            'low_value' => $segments->get('low_value', collect())->count()
        ];
    }
    
    private function getActiveOrders()
    {
        return Laundry::whereIn('status', ['pending', 'processing', 'ready', 'out_for_delivery'])
            ->count();
    }
    
    private function getPendingPickups()
    {
        return PickupRequest::where('status', 'pending')
            ->count();
    }
    
    private function getTodayRevenue()
    {
        return (float) Laundry::whereDate('created_at', today())
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');
    }
    
    private function getTodayOrders()
    {
        return Laundry::whereDate('created_at', today())
            ->count();
    }
    
    private function getStaffUtilization()
    {
        // Mock data - implement based on your staff tracking system
        return [
            'active_staff' => 5,
            'total_staff' => 8,
            'utilization_rate' => 62.5
        ];
    }
    
    private function getBranchPerformance()
    {
        return DB::table('laundries')
            ->join('branches', 'laundries.branch_id', '=', 'branches.id')
            ->select('branches.name', 'branches.id')
            ->selectRaw('COUNT(*) as orders_today, SUM(total_amount) as revenue_today')
            ->whereDate('laundries.created_at', today())
            ->groupBy('branches.id', 'branches.name')
            ->get()
            ->map(fn($branch) => [
                'branch_name' => $branch->name,
                'orders' => $branch->orders_today,
                'revenue' => (float) $branch->revenue_today
            ]);
    }
    
    private function getAtRiskCustomers()
    {
        // Customers who haven't ordered in 14-29 days (before they become inactive)
        return Laundry::selectRaw('customer_id, MAX(created_at) as last_order')
            ->groupBy('customer_id')
            ->having('last_order', '>=', Carbon::now()->subDays(29))
            ->having('last_order', '<', Carbon::now()->subDays(14))
            ->count();
    }
}
