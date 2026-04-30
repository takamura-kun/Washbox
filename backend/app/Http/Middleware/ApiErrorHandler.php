<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Authorization\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;

class ApiErrorHandler
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            Log::warning("Model not found", [
                'model' => get_class($e->getModel()),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'NOT_FOUND',
                'message' => 'The requested resource was not found',
            ], 404);

        } catch (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'error' => 'NOT_FOUND',
                'message' => 'Route not found',
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'UNAUTHENTICATED',
                'message' => 'You are not authenticated',
            ], 401);

        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'UNAUTHORIZED',
                'message' => 'You are not authorized to perform this action',
            ], 403);

        } catch (HttpException $e) {
            $statusCode = $e->getStatusCode();
            
            return response()->json([
                'success' => false,
                'error' => 'HTTP_ERROR',
                'message' => $e->getMessage() ?: "HTTP Error {$statusCode}",
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error("Unexpected error in API request", [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'path' => $request->path(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => config('app.debug') 
                    ? $e->getMessage() 
                    : 'An internal server error occurred. Please try again later.',
            ], 500);
        }
    }
}
