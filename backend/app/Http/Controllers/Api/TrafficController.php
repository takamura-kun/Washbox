<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TrafficController extends Controller
{
    public function current(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
}
