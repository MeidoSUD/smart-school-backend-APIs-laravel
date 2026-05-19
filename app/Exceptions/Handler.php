<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = 500;

            if ($e instanceof ValidationException) {
                $status = 422;
            } elseif ($e instanceof ModelNotFoundException) {
                $status = 404;
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Server Error',
                'code' => $status,
                'exception' => class_basename($e),
                'errors' => $e instanceof ValidationException
                    ? $e->errors()
                    : null,
                'file' => app()->isLocal()
                    ? $e->getFile()
                    : null,
                'line' => app()->isLocal()
                    ? $e->getLine()
                    : null,
            ], $status);
        }

        return parent::render($request, $e);
    }
}
