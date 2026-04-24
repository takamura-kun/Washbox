<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\PickupRequest;
use App\Models\Laundry;

class DashboardSyncService
{
    // Cache keys for dashboard data
    const CACHE_KEYS = [
        'dashboard_pickup_stats' => 'dashboard:pickup_stats',
        'dashboard_recent_pickups' => 'dashboard:recent_pickups',
        'dashboard_active_orders' => 'dashboard:active_orders',
        'dashboard_daily_stats' => 'dashboard:daily_stats',
    ];

    // Cache duration in seconds (5 minutes for most data)
    const CACHE_DURATION = 300;
    const REALTIME_CACHE_DURATION = 30; // 30 seconds for real-time data

    /**
     * Clear all dashboard caches when data changes
     */
    public static function clearDashboardCache()
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
        
        // Also clear any date-specific caches
        $today = now()->format('Y-m-d');
        Cache::forget("dashboard:stats:{$today}");
        Cache::forget("dashboard:pickups:{$today}");
    }

    /**
     * Get real-time pickup statistics
     */
    public static function getPickupStats($forceRefresh = false)
    {
        $cacheKey = self::CACHE_KEYS['dashboard_pickup_stats'];
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::REALTIME_CACHE_DURATION, function () {
            return [
                'pending' => PickupRequest::where('status', 'pending')->count(),
                'accepted' => PickupRequest::where('status', 'accepted')->count(),
                'en_route' => PickupRequest::where('status', 'en_route')->count(),
                'picked_up' => PickupRequest::where('status', 'picked_up')->count(),
                'total_today' => PickupRequest::whereDate('created_at', today())->count(),
                'active_total' => PickupRequest::whereIn('status', ['pending', 'accepted', 'en_route'])->count(),
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get recent pickup requests with real-time updates
     */
    public static function getRecentPickups($limit = 10, $forceRefresh = false)
    {
        $cacheKey = self::CACHE_KEYS['dashboard_recent_pickups'] . ":{$limit}";
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::REALTIME_CACHE_DURATION, function () use ($limit) {
            return PickupRequest::with(['customer', 'branch', 'assignedStaff'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($pickup) {
                    return [
                        'id' => $pickup->id,
                        'customer_name' => $pickup->customer->name ?? 'Unknown',
                        'pickup_address' => $pickup->pickup_address,
                        'status' => $pickup->status,
                        'branch_name' => $pickup->branch->name ?? 'Unknown',
                        'assigned_staff' => $pickup->assignedStaff->name ?? null,
                        'created_at' => $pickup->created_at->toISOString(),
                        'preferred_date' => $pickup->preferred_date->format('Y-m-d'),
                        'preferred_time' => $pickup->preferred_time,
                    ];
                });
        });
    }

    /**
     * Get active orders with real-time updates
     */
    public static function getActiveOrders($forceRefresh = false)
    {
        $cacheKey = self::CACHE_KEYS['dashboard_active_orders'];
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::REALTIME_CACHE_DURATION, function () {
            return [
                'laundries' => Laundry::whereIn('status', [
                    'pending', 'confirmed', 'in_progress', 'ready_for_pickup', 'out_for_delivery'
                ])->with(['customer', 'branch'])->get(),
                
                'pickups' => PickupRequest::whereIn('status', ['pending', 'accepted', 'en_route'])
                    ->with(['customer', 'branch', 'assignedStaff'])
                    ->get(),
                    
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get daily statistics
     */
    public static function getDailyStats($date = null, $forceRefresh = false)
    {
        $date = $date ?? today();
        $cacheKey = "dashboard:stats:{$date->format('Y-m-d')}";
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($date) {
            return [
                'pickups_created' => PickupRequest::whereDate('created_at', $date)->count(),
                'pickups_completed' => PickupRequest::whereDate('picked_up_at', $date)->count(),
                'laundries_created' => Laundry::whereDate('created_at', $date)->count(),
                'laundries_completed' => Laundry::whereDate('completed_at', $date)->count(),
                'revenue' => Laundry::whereDate('completed_at', $date)->sum('total_amount'),
                'date' => $date->format('Y-m-d'),
            ];
        });
    }

    /**
     * Invalidate cache when pickup status changes
     */
    public static function onPickupStatusChanged($pickupRequest)
    {
        self::clearDashboardCache();
        
        // Broadcast real-time update if you have WebSocket/Pusher
        // broadcast(new PickupStatusUpdated($pickupRequest));
    }

    /**
     * Invalidate cache when new pickup is created
     */
    public static function onPickupCreated($pickupRequest)
    {
        self::clearDashboardCache();
        
        // Broadcast real-time update
        // broadcast(new NewPickupCreated($pickupRequest));
    }

    /**
     * Force refresh all dashboard data
     */
    public static function forceRefreshAll()
    {
        self::clearDashboardCache();
        
        // Pre-warm cache with fresh data
        self::getPickupStats(true);
        self::getRecentPickups(10, true);
        self::getActiveOrders(true);
        self::getDailyStats(null, true);
        
        return [
            'status' => 'success',
            'message' => 'Dashboard cache refreshed',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get cache status for debugging
     */
    public static function getCacheStatus()
    {
        $status = [];
        
        foreach (self::CACHE_KEYS as $name => $key) {
            $status[$name] = [
                'cached' => Cache::has($key),
                'ttl' => Cache::has($key) ? 'active' : 'expired',
            ];
        }
        
        return $status;
    }
}