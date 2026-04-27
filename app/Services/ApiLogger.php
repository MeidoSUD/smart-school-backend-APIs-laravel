<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class ApiLogger
{
    /**
     * Log API request and response
     */
    public static function log($message, $data = null, $type = 'info')
    {
        $request = request();
        
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id() ?? 'guest',
            'message' => $message,
        ];
        
        if ($data) {
            $logData['data'] = $data;
        }
        
        // Log as array for better readability
        Log::$type('API: ' . json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Log incoming request
     */
    public static function logRequest($controller, $method, $data = null)
    {
        $request = request();
        
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'controller' => $controller,
            'method' => $method,
            'ip' => $request->ip(),
            'method_http' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id() ?? $request->user()?->id ?? 'guest',
            'user_role' => $request->user()?->role ?? 'guest',
        ];
        
        // Sanitize sensitive data
        $sanitizedData = self::sanitize($data);
        if ($sanitizedData) {
            $logData['request_data'] = $sanitizedData;
        }
        
        Log::info('API_REQUEST: ' . json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Log outgoing response
     */
    public static function logResponse($controller, $method, $response, $statusCode = 200)
    {
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'controller' => $controller,
            'method' => $method,
            'status_code' => $statusCode,
            'user_id' => auth()->id() ?? request()->user()?->id ?? 'guest',
        ];
        
        // Only log first 100 chars of response to avoid huge logs
        $responsePreview = is_array($response) 
            ? json_encode(array_slice($response, 0, 10), JSON_PRETTY_PRINT)
            : (is_string($response) ? substr($response, 0, 500) : gettype($response));
        
        $logData['response_preview'] = $responsePreview;
        
        $level = $statusCode >= 400 ? 'warning' : 'info';
        Log::$level('API_RESPONSE: ' . json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Log error
     */
    public static function logError($controller, $method, $error, $context = [])
    {
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'controller' => $controller,
            'method' => $method,
            'error' => $error,
            'user_id' => auth()->id() ?? request()->user()?->id ?? 'guest',
        ];
        
        if (!empty($context)) {
            $logData['context'] = self::sanitize($context);
        }
        
        Log::error('API_ERROR: ' . json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Log authentication events
     */
    public static function logAuth($event, $username, $success, $userId = null)
    {
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'event' => $event,
            'username' => $username,
            'success' => $success,
            'user_id' => $userId,
            'ip' => request()->ip(),
        ];
        
        $level = $success ? 'info' : 'warning';
        Log::$level('API_AUTH: ' . json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Sanitize sensitive data for logging
     */
    private static function sanitize($data)
    {
        if (!$data) {
            return null;
        }
        
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // Skip sensitive fields
                if (in_array(strtolower($key), ['password', 'token', 'secret', 'key', 'credit_card', 'card_number'])) {
                    $sanitized[$key] = '***HIDDEN***';
                } elseif (is_array($value)) {
                    $sanitized[$key] = self::sanitize($value);
                } else {
                    $sanitized[$key] = $value;
                }
            }
            return $sanitized;
        }
        
        return $data;
    }
}