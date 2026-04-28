<?php

namespace App\Services;

use App\Models\ServiceArea;
use Illuminate\Support\Str;

class DeliveryFeeService
{
    /**
     * Check if address is in free delivery zone
     */
    public function isFreeDeliveryZone(string $address, int $branchId): bool
    {
        $freeAreas = ServiceArea::active()
            ->free()
            ->forBranch($branchId)
            ->get();

        foreach ($freeAreas as $area) {
            if ($this->addressMatchesArea($address, $area->area_name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get delivery fee for address
     */
    public function getDeliveryFee(string $address, int $branchId): array
    {
        // Check if in free zone
        if ($this->isFreeDeliveryZone($address, $branchId)) {
            return [
                'is_free' => true,
                'fee' => 0.00,
                'area_name' => $this->getMatchedAreaName($address, $branchId),
                'message' => 'Free pickup and delivery',
            ];
        }

        // Check if in paid zone
        $paidArea = $this->getMatchedPaidArea($address, $branchId);
        if ($paidArea) {
            return [
                'is_free' => false,
                'fee' => $paidArea->delivery_fee,
                'area_name' => $paidArea->area_name,
                'message' => "Delivery fee: ₱{$paidArea->delivery_fee}",
            ];
        }

        // Outside service area
        return [
            'is_free' => false,
            'fee' => null,
            'area_name' => null,
            'message' => 'Outside service area. Please contact us for delivery options.',
        ];
    }

    /**
     * Check if address matches area name
     */
    private function addressMatchesArea(string $address, string $areaName): bool
    {
        $address = Str::lower($address);
        $areaName = Str::lower($areaName);

        // Direct match
        if (Str::contains($address, $areaName)) {
            return true;
        }

        // Handle variations
        $variations = $this->getAreaVariations($areaName);
        foreach ($variations as $variation) {
            if (Str::contains($address, $variation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get area name variations for matching
     */
    private function getAreaVariations(string $areaName): array
    {
        $variations = [];
        $areaName = Str::lower($areaName);

        // Remove common suffixes
        $withoutCity = str_replace(' city', '', $areaName);
        $withoutMunicipality = str_replace(' municipality', '', $areaName);

        $variations[] = $areaName;
        $variations[] = $withoutCity;
        $variations[] = $withoutMunicipality;

        // Add common abbreviations
        if ($areaName === 'dumaguete city') {
            $variations[] = 'dumaguete';
            $variations[] = 'dgte';
        }

        return array_unique($variations);
    }

    /**
     * Get matched area name from address
     */
    private function getMatchedAreaName(string $address, int $branchId): ?string
    {
        $areas = ServiceArea::active()
            ->forBranch($branchId)
            ->get();

        foreach ($areas as $area) {
            if ($this->addressMatchesArea($address, $area->area_name)) {
                return $area->area_name;
            }
        }

        return null;
    }

    /**
     * Get matched paid area
     */
    private function getMatchedPaidArea(string $address, int $branchId): ?ServiceArea
    {
        $paidAreas = ServiceArea::active()
            ->where('is_free', false)
            ->forBranch($branchId)
            ->get();

        foreach ($paidAreas as $area) {
            if ($this->addressMatchesArea($address, $area->area_name)) {
                return $area;
            }
        }

        return null;
    }

    /**
     * Get all service areas for branch
     */
    public function getServiceAreas(int $branchId): array
    {
        $areas = ServiceArea::active()
            ->forBranch($branchId)
            ->orderBy('is_free', 'desc')
            ->orderBy('area_name')
            ->get();

        return [
            'free_areas' => $areas->where('is_free', true)->values(),
            'paid_areas' => $areas->where('is_free', false)->values(),
        ];
    }
}
