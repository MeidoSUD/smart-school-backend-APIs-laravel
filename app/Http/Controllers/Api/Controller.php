<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use App\Services\ApiLogger;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Track controller name for logging
     */
    protected $controllerName = '';

    protected function setControllerName($name)
    {
        $this->controllerName = $name;
    }

    /**
     * Return a success JSON response with logging
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        // Log successful response
        if ($this->controllerName) {
            ApiLogger::logResponse(
                $this->controllerName,
                $this->getCurrentMethod(),
                $data,
                $statusCode
            );
        }

        $response = [
            'status' => 'success',
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        $response['timestamp'] = now()->toDateTimeString();

        return response()->json($response, $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Return an error JSON response with logging
     */
    protected function errorResponse(?string $message = null, mixed $errors = null, int $statusCode = 400): JsonResponse
    {
        // Log error response
        if ($this->controllerName) {
            ApiLogger::logError(
                $this->controllerName,
                $this->getCurrentMethod(),
                $message ?? 'Unknown error',
                ['errors' => $errors]
            );
        }

        $response = [
            'status' => 'error',
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $response['timestamp'] = now()->toDateTimeString();

        return response()->json($response, $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Get current method name
     */
    private function getCurrentMethod(): string
    {
        $trace = [];
        return $trace[1]['function'] ?? 'unknown';
    }

    /**
     * Log incoming request
     */
    protected function logRequest($data = null)
    {
        if ($this->controllerName) {
            // Get request input (sanitized)
            $input = request()->except(['password', 'token']);
            
            ApiLogger::logRequest(
                $this->controllerName,
                $this->getCurrentMethod(),
                $input
            );
        }
    }
}