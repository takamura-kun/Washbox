<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BranchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with branch guard
        if (!Auth::guard('branch')->check()) {
            // Allow regular authenticated users with branch_id (admin/staff)
            if (Auth::check() && Auth::user()->branch_id) {
                return $next($request);
            }
            
            return redirect()->route('branch.login')
                ->with('error', 'Please login to access this page.');
        }

        // Check if branch is active
        $branch = Auth::guard('branch')->user();
        if (!$branch->is_active) {
            Auth::guard('branch')->logout();
            return redirect()->route('branch.login')
                ->with('error', 'Your branch account has been deactivated. Please contact administrator.');
        }

        return $next($request);
    }
}
