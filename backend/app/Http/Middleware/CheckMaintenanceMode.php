<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always allow admin routes (login, logout, and all admin/* routes)
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        $maintenanceMode = SystemSetting::get('maintenance_mode', false);
        
        if (!$maintenanceMode) {
            return $next($request);
        }

        // For API requests, return JSON response
        if ($request->is('api/*') || $request->expectsJson()) {
            $message = SystemSetting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon!');
            $maintenanceEnd = SystemSetting::get('maintenance_end');
            
            return response()->json([
                'error' => 'Service Unavailable',
                'message' => $message,
                'maintenance_mode' => true,
                'maintenance_end' => $maintenanceEnd,
            ], 503);
        }

        // For web requests (staff and customers), show maintenance page
        $message = SystemSetting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon!');
        $maintenanceEnd = SystemSetting::get('maintenance_end');
        
        return response()->view('maintenance', [
            'message' => $message,
            'maintenanceEnd' => $maintenanceEnd
        ], 503);
    }
}
