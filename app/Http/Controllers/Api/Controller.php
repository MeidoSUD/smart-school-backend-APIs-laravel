<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a success JSON response
     * Maps to: CodeIgniter's $this->api_success()
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Return an error JSON response
     * Maps to: CodeIgniter's $this->api_error()
     *
     * @param string|null $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(?string $message = null, mixed $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'status' => 'error',
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}