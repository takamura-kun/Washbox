<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServiceAvailabilityService;
use App\Services\DeliveryFeeService;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class ServiceStatusController extends Controller
{
    /**
     * Get current service availability status
     * 
     * GET /api/v1/service-status
     */
    public function index()
    {
        try {
            $status = ServiceAvailabilityService::getServiceStatus();
            
            // Add additional system information
            $status['business_hours'] = $this->getBusinessHours();
            $status['contact_info'] = [
                'shop_name' => SystemSetting::get('shop_name', 'WashBox'),
                'contact_number' => SystemSetting::get('contact_number'),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pickup/delivery service configuration
     * 
     * GET /api/v1/service-config
     */
    public function getServiceConfig()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'pickup' => [
                        'enabled' => ServiceAvailabilityService::isPickupEnabled(),
                        'require_proof_photo' => (bool) SystemSetting::get('require_customer_proof_photo', true),
                        'default_fee' => (float) SystemSetting::get('default_pickup_fee', 50),
                        'advance_booking_min_days' => (int) SystemSetting::get('pickup_advance_days_min', 1),
                        'advance_booking_max_days' => (int) SystemSetting::get('pickup_advance_days_max', 7),
                        'free_delivery_message' => 'Free pickup & delivery within Dumaguete and Sibulan',
                    ],
                    'delivery' => [
                        'enabled' => ServiceAvailabilityService::isDeliveryEnabled(),
                        'default_fee' => (float) SystemSetting::get('default_delivery_fee', 50),
                        'max_radius_km' => (int) SystemSetting::get('max_service_radius_km', 10),
                    ],
                    'available_service_types' => ServiceAvailabilityService::getAvailableServiceTypes(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check delivery fee for address
     * 
     * POST /api/v1/check-delivery-fee
     */
    public function checkDeliveryFee(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        try {
            $deliveryFeeService = new DeliveryFeeService();
            $feeInfo = $deliveryFeeService->getDeliveryFee(
                $request->address,
                $request->branch_id
            );

            return response()->json([
                'success' => true,
                'data' => $feeInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check delivery fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service areas for branch
     * 
     * GET /api/v1/service-areas/{branchId}
     */
    public function getServiceAreas($branchId)
    {
        try {
            $deliveryFeeService = new DeliveryFeeService();
            $areas = $deliveryFeeService->getServiceAreas($branchId);

            return response()->json([
                'success' => true,
                'data' => $areas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service areas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current business hours
     */
    private function getBusinessHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];
        
        foreach ($days as $day) {
            $isOpen = (bool) SystemSetting::get("hours_{$day}_open", true);
            
            if ($isOpen) {
                $hours[$day] = [
                    'open' => SystemSetting::get("hours_{$day}_start", '07:00'),
                    'close' => SystemSetting::get("hours_{$day}_end", '20:00'),
                    'status' => 'open'
                ];
            } else {
                $hours[$day] = 'closed';
            }
        }
        
        return $hours;
    }
}