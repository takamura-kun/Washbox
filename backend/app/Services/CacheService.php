<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // Cache TTL constants (in seconds)
    const BRANCHES_CACHE_TTL = 3600;      // 1 hour
    const SERVICES_CACHE_TTL = 3600;      // 1 hour
    const PROMOTIONS_CACHE_TTL = 1800;    // 30 minutes
    const CUSTOMER_PROFILE_TTL = 300;     // 5 minutes
    const BRANCH_OPERATING_HOURS_TTL = 3600; // 1 hour

    /**
     * Get all branches with caching
     */
    public static function getBranches($fresh = false)
    {
        $cacheKey = 'branches:all';

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::BRANCHES_CACHE_TTL, function () {
            Log::info("Fetching branches from database");
            return \App\Models\Branch::where('is_active', true)
                ->select('id', 'name', 'code', 'city', 'address', 'phone', 'email')
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get all services with caching
     */
    public static function getServices($fresh = false)
    {
        $cacheKey = 'services:all';

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::SERVICES_CACHE_TTL, function () {
            Log::info("Fetching services from database");
            return \App\Models\Service::where('is_active', true)
                ->select('id', 'name', 'service_type', 'pricing_type', 'price_per_load', 'price_per_piece')
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get all active promotions with caching
     */
    public static function getPromotions($fresh = false)
    {
        $cacheKey = 'promotions:all';

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::PROMOTIONS_CACHE_TTL, function () {
            Log::info("Fetching promotions from database");
            return \App\Models\Promotion::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->select('id', 'name', 'discount_type', 'discount_value', 'min_amount')
                ->orderBy('priority')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get customer profile with short cache
     */
    public static function getCustomerProfile($customerId, $fresh = false)
    {
        $cacheKey = "customer:profile:{$customerId}";

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CUSTOMER_PROFILE_TTL, function () use ($customerId) {
            Log::info("Fetching customer profile from database", ['customer_id' => $customerId]);
            return \App\Models\Customer::findOrFail($customerId);
        });
    }

    /**
     * Invalidate all branches cache
     */
    public static function invalidateBranchesCache()
    {
        Cache::forget('branches:all');
        Log::info("Invalidated branches cache");
    }

    /**
     * Invalidate all services cache
     */
    public static function invalidateServicesCache()
    {
        Cache::forget('services:all');
        Log::info("Invalidated services cache");
    }

    /**
     * Invalidate all promotions cache
     */
    public static function invalidatePromotionsCache()
    {
        Cache::forget('promotions:all');
        Log::info("Invalidated promotions cache");
    }

    /**
     * Invalidate customer profile cache
     */
    public static function invalidateCustomerCache($customerId)
    {
        Cache::forget("customer:profile:{$customerId}");
        Log::info("Invalidated customer cache", ['customer_id' => $customerId]);
    }

    /**
     * Invalidate all cache
     */
    public static function invalidateAll()
    {
        Cache::flush();
        Log::info("Invalidated all cache");
    }

    /**
     * Get cache stats
     */
    public static function getStats()
    {
        return [
            'driver' => config('cache.default'),
            'branches_cached' => Cache::has('branches:all'),
            'services_cached' => Cache::has('services:all'),
            'promotions_cached' => Cache::has('promotions:all'),
        ];
    }
}
