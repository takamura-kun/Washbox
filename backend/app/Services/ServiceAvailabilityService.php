<?php

namespace App\Services;

use App\Models\SystemSetting;

class ServiceAvailabilityService
{
    /**
     * Check if pickup service is enabled system-wide
     */
    public static function isPickupEnabled(): bool
    {
        return (bool) SystemSetting::get('enable_pickup', true);
    }

    /**
     * Check if delivery service is enabled system-wide
     */
    public static function isDeliveryEnabled(): bool
    {
        return (bool) SystemSetting::get('enable_delivery', true);
    }

    /**
     * Check if both pickup and delivery are enabled
     */
    public static function areBothServicesEnabled(): bool
    {
        return self::isPickupEnabled() && self::isDeliveryEnabled();
    }

    /**
     * Get available service types based on current settings
     */
    public static function getAvailableServiceTypes(): array
    {
        $types = [];
        
        if (self::isPickupEnabled()) {
            $types[] = 'pickup_only';
        }
        
        if (self::isDeliveryEnabled()) {
            $types[] = 'delivery_only';
        }
        
        if (self::areBothServicesEnabled()) {
            $types[] = 'both';
        }
        
        return $types;
    }

    /**
     * Validate if a requested service type is available
     */
    public static function isServiceTypeAvailable(string $serviceType): bool
    {
        return in_array($serviceType, self::getAvailableServiceTypes());
    }

    /**
     * Get service availability status for API responses
     */
    public static function getServiceStatus(): array
    {
        return [
            'pickup_enabled' => self::isPickupEnabled(),
            'delivery_enabled' => self::isDeliveryEnabled(),
            'available_service_types' => self::getAvailableServiceTypes(),
        ];
    }

    /**
     * Get user-friendly message when services are disabled
     */
    public static function getDisabledServiceMessage(string $serviceType): string
    {
        $messages = [
            'pickup_only' => 'Pickup service is currently unavailable. Please contact us directly.',
            'delivery_only' => 'Delivery service is currently unavailable. Please visit our branch.',
            'both' => 'Pickup and delivery services are currently unavailable. Please visit our branch.',
        ];

        return $messages[$serviceType] ?? 'This service is currently unavailable.';
    }
}