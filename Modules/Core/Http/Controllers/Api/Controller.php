<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Modules\Core\Services\ApiLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $controllerName = '';

    protected function setControllerName($name)
    {
        $this->controllerName = $name;
    }

    protected function successResponse(mixed $data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
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

    protected function errorResponse(?string $message = null, mixed $errors = null, int $statusCode = 400): JsonResponse
    {
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

    private function getCurrentMethod(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($trace as $frame) {
            $function = $frame['function'] ?? '';
            if (!in_array($function, ['getCurrentMethod', 'successResponse', 'errorResponse', 'logRequest'])) {
                return $function;
            }
        }
        return 'unknown';
    }

protected function logRequest($data = null)
    {
        if ($this->controllerName) {
            $input = request()->except(['password', 'token']);

            ApiLogger::logRequest(
                $this->controllerName,
                $this->getCurrentMethod(),
                $input
            );
        }
    }

    /**
     * Serve a stored upload as a file download. Checks the private storage
     * disk first (student-uploaded files) then falls back to public/uploads
     * (staff/school-uploaded files).
     */
    protected function sendStoredFile(?string $filename, string ...$directories): BinaryFileResponse|JsonResponse
    {
        if (! $filename) {
            return $this->errorResponse('File not found', null, 404);
        }

        foreach ($directories as $dir) {
            $candidates = [
                storage_path('app/' . trim($dir, '/') . '/' . $filename),
                public_path(trim($dir, '/') . '/' . $filename),
            ];

            foreach ($candidates as $path) {
                if (file_exists($path) && is_file($path)) {
                    return response()->download($path, $filename);
                }
            }
        }

        return $this->errorResponse('File not found', null, 404);
    }
}
