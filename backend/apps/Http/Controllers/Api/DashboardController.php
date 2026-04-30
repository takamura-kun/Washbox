<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get real-time dashboard statistics
     */
    public function getStats(Request $request)
    {
        $forceRefresh = $request->boolean('refresh', false);
        
        $stats = DashboardSyncService::getPickupStats($forceRefresh);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'cache_status' => $forceRefresh ? 'refreshed' : 'cached'
        ]);
    }

    /**
     * Get recent pickup requests
     */
    public function getRecentPickups(Request $request)
    {
        $limit = $request->integer('limit', 10);
        $forceRefresh = $request->boolean('refresh', false);
        
        $pickups = DashboardSyncService::getRecentPickups($limit, $forceRefresh);
        
        return response()->json([
            'success' => true,
            'data' => [
                'pickups' => $pickups,
                'count' => $pickups->count()
            ]
        ]);
    }

    /**
     * Get active orders (pickups + laundries)
     */
    public function getActiveOrders(Request $request)
    {
        $forceRefresh = $request->boolean('refresh', false);
        
        $orders = DashboardSyncService::getActiveOrders($forceRefresh);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get daily statistics
     */
    public function getDailyStats(Request $request)
    {
        $date = $request->date('date') ? 
            \Carbon\Carbon::parse($request->date('date')) : 
            today();
            
        $forceRefresh = $request->boolean('refresh', false);
        
        $stats = DashboardSyncService::getDailyStats($date, $forceRefresh);
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Force refresh all dashboard data
     */
    public function forceRefresh()
    {
        $result = DashboardSyncService::forceRefreshAll();
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard data refreshed successfully',
            'data' => $result
        ]);
    }

    /**
     * Get cache status for debugging
     */
    public function getCacheStatus()
    {
        $status = DashboardSyncService::getCacheStatus();
        
        return response()->json([
            'success' => true,
            'data' => [
                'cache_status' => $status,
                'server_time' => now()->toISOString(),
                'cache_driver' => config('cache.default')
            ]
        ]);
    }

    /**
     * Get comprehensive dashboard data in one call
     */
    public function getDashboardData(Request $request)
    {
        $forceRefresh = $request->boolean('refresh', false);
        
        $data = [
            'stats' => DashboardSyncService::getPickupStats($forceRefresh),
            'recent_pickups' => DashboardSyncService::getRecentPickups(5, $forceRefresh),
            'active_orders' => DashboardSyncService::getActiveOrders($forceRefresh),
            'daily_stats' => DashboardSyncService::getDailyStats(null, $forceRefresh),
            'last_updated' => now()->toISOString()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'cache_refreshed' => $forceRefresh
        ]);
    }

    /**
     * Clear specific cache keys
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'keys' => 'array',
            'keys.*' => 'string'
        ]);
        
        $keys = $request->input('keys', []);
        
        if (empty($keys)) {
            // Clear all dashboard cache
            DashboardSyncService::clearDashboardCache();
            $message = 'All dashboard cache cleared';
        } else {
            // Clear specific keys
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            $message = 'Specific cache keys cleared';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'cleared_keys' => $keys ?: 'all'
        ]);
    }
}