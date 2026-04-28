<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function profiles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 'default',
                    'name' => 'Standard Vehicle',
                    'capacity' => 50,
                    'max_stops' => 15,
                    'avg_speed' => 35,
                    'fuel_efficiency' => 12,
                    'operating_cost' => 0.5,
                    'time_windows' => [
                        'start' => '08:00',
                        'end' => '18:00'
                    ]
                ]
            ]
        ]);
    }
}
