<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhilippineGeocodingService
{
    // Known coordinates for Dumaguete City landmarks
    const DUMAGUETE_LANDMARKS = [
        'dr_v_locsin' => ['lat' => 9.3065, 'lng' => 123.3045],
        'rizal_boulevard' => ['lat' => 9.3089, 'lng' => 123.3065],
        'silliman_university' => ['lat' => 9.3103, 'lng' => 123.3017],
        'robinsons_dumaguete' => ['lat' => 9.3034, 'lng' => 123.3028],
        'lee_plaza' => ['lat' => 9.3076, 'lng' => 123.3042],
        'dumaguete_center' => ['lat' => 9.3068, 'lng' => 123.3054],
    ];

    // Geographic bounds
    const PHILIPPINES_BOUNDS = [
        'north' => 21.0,
        'south' => 4.0,
        'east' => 127.0,
        'west' => 116.0
    ];

    const DUMAGUETE_BOUNDS = [
        'north' => 9.35,
        'south' => 9.25,
        'east' => 123.35,
        'west' => 123.25
    ];

    /**
     * Geocode a Philippine address with validation and fallbacks
     */
    public static function geocodeAddress($address)
    {
        if (empty($address)) {
            return self::getDumagueteCenter();
        }

        // Format address for better accuracy
        $formattedAddress = self::formatAddress($address);
        
        Log::info("Geocoding address: {$address} -> {$formattedAddress}");

        // Try geocoding with formatted address
        $result = self::performGeocoding($formattedAddress);
        
        if ($result && self::isValidPhilippineCoordinates($result['lat'], $result['lng'])) {
            // Validate coordinates are in correct location
            $validated = self::validateCoordinates($address, $result['lat'], $result['lng']);
            
            return [
                'latitude' => $validated['lat'],
                'longitude' => $validated['lng'],
                'formatted_address' => $formattedAddress,
                'source' => $validated['corrected'] ? 'fallback' : 'geocoded',
                'corrected' => $validated['corrected'] ?? false
            ];
        }

        // Geocoding failed, use fallback
        Log::warning("Geocoding failed for: {$address}. Using fallback.");
        $fallback = self::getFallbackCoordinates($address);
        
        return [
            'latitude' => $fallback['lat'],
            'longitude' => $fallback['lng'],
            'formatted_address' => $formattedAddress,
            'source' => 'fallback',
            'corrected' => true
        ];
    }

    /**
     * Format address for better geocoding accuracy
     */
    private static function formatAddress($address)
    {
        $formatted = trim($address);

        // Expand common abbreviations
        $replacements = [
            '/\bDr\.?\s+V\.?\s+Locsin\b/i' => 'Doctor Vicente Locsin Street',
            '/\bDr\.?\s+Locsin\b/i' => 'Doctor Vicente Locsin Street',
            '/\bRizal\s+Blvd\b/i' => 'Rizal Boulevard',
            '/\bSt\.?\b/i' => 'Street',
            '/\bAve\.?\b/i' => 'Avenue',
            '/\bBlvd\.?\b/i' => 'Boulevard',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $formatted = preg_replace($pattern, $replacement, $formatted);
        }

        // Ensure Dumaguete addresses have full location info
        if (stripos($formatted, 'dumaguete') !== false) {
            // Add province if missing
            if (stripos($formatted, 'negros oriental') === false) {
                $formatted = preg_replace('/dumaguete\s*city/i', 'Dumaguete City, Negros Oriental', $formatted);
            }
            
            // Add country if missing
            if (stripos($formatted, 'philippines') === false) {
                $formatted .= ', Philippines';
            }
        }

        return $formatted;
    }

    /**
     * Perform actual geocoding using Nominatim
     */
    private static function performGeocoding($address)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'WashBox Laundry App/1.0'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'ph'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data) && isset($data[0])) {
                    return [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon'],
                        'display_name' => $data[0]['display_name'] ?? $address
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Geocoding API error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if coordinates are within Philippines bounds
     */
    private static function isValidPhilippineCoordinates($lat, $lng)
    {
        return $lat >= self::PHILIPPINES_BOUNDS['south'] &&
               $lat <= self::PHILIPPINES_BOUNDS['north'] &&
               $lng >= self::PHILIPPINES_BOUNDS['west'] &&
               $lng <= self::PHILIPPINES_BOUNDS['east'];
    }

    /**
     * Check if coordinates are within Dumaguete bounds
     */
    private static function isInDumaguete($lat, $lng)
    {
        return $lat >= self::DUMAGUETE_BOUNDS['south'] &&
               $lat <= self::DUMAGUETE_BOUNDS['north'] &&
               $lng >= self::DUMAGUETE_BOUNDS['west'] &&
               $lng <= self::DUMAGUETE_BOUNDS['east'];
    }

    /**
     * Validate coordinates and correct if necessary
     */
    private static function validateCoordinates($address, $lat, $lng)
    {
        // If coordinates are not in Philippines, use fallback
        if (!self::isValidPhilippineCoordinates($lat, $lng)) {
            Log::warning("Coordinates {$lat}, {$lng} not in Philippines for address: {$address}");
            $fallback = self::getFallbackCoordinates($address);
            return [
                'lat' => $fallback['lat'],
                'lng' => $fallback['lng'],
                'corrected' => true
            ];
        }

        // If it's a Dumaguete address but coordinates are not in Dumaguete
        if (stripos($address, 'dumaguete') !== false && !self::isInDumaguete($lat, $lng)) {
            Log::warning("Dumaguete address but coordinates {$lat}, {$lng} not in Dumaguete bounds");
            $fallback = self::getDumagueteFallback($address);
            return [
                'lat' => $fallback['lat'],
                'lng' => $fallback['lng'],
                'corrected' => true
            ];
        }

        // Coordinates are valid
        return [
            'lat' => $lat,
            'lng' => $lng,
            'corrected' => false
        ];
    }

    /**
     * Get fallback coordinates based on address content
     */
    private static function getFallbackCoordinates($address)
    {
        $addr = strtolower($address);

        // Check for Dumaguete landmarks
        if (stripos($addr, 'dumaguete') !== false) {
            return self::getDumagueteFallback($address);
        }

        // Default to Philippines center (Manila area)
        return ['lat' => 14.5995, 'lng' => 120.9842];
    }

    /**
     * Get specific fallback for Dumaguete addresses
     */
    private static function getDumagueteFallback($address)
    {
        $addr = strtolower($address);

        // Check for specific landmarks
        if (strpos($addr, 'dr. v locsin') !== false || 
            strpos($addr, 'dr v locsin') !== false || 
            strpos($addr, 'locsin') !== false) {
            return self::DUMAGUETE_LANDMARKS['dr_v_locsin'];
        }

        if (strpos($addr, 'rizal boulevard') !== false || strpos($addr, 'rizal blvd') !== false) {
            return self::DUMAGUETE_LANDMARKS['rizal_boulevard'];
        }

        if (strpos($addr, 'silliman') !== false) {
            return self::DUMAGUETE_LANDMARKS['silliman_university'];
        }

        if (strpos($addr, 'robinsons') !== false) {
            return self::DUMAGUETE_LANDMARKS['robinsons_dumaguete'];
        }

        if (strpos($addr, 'lee plaza') !== false) {
            return self::DUMAGUETE_LANDMARKS['lee_plaza'];
        }

        // Default to Dumaguete center
        return self::DUMAGUETE_LANDMARKS['dumaguete_center'];
    }

    /**
     * Get Dumaguete city center coordinates
     */
    private static function getDumagueteCenter()
    {
        return [
            'latitude' => self::DUMAGUETE_LANDMARKS['dumaguete_center']['lat'],
            'longitude' => self::DUMAGUETE_LANDMARKS['dumaguete_center']['lng'],
            'formatted_address' => 'Dumaguete City, Negros Oriental, Philippines',
            'source' => 'default',
            'corrected' => true
        ];
    }

    /**
     * Reverse geocode coordinates to address
     */
    public static function reverseGeocode($lat, $lng)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'WashBox Laundry App/1.0'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'zoom' => 16
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['display_name'])) {
                    return [
                        'address' => $data['display_name'],
                        'components' => $data['address'] ?? []
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Reverse geocoding error: " . $e->getMessage());
        }

        return [
            'address' => "{$lat}, {$lng}",
            'components' => []
        ];
    }
}