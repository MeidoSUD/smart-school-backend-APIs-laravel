<?php
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\AuthController;
use Modules\Core\Http\Controllers\Api\UserController;

Route::prefix('api')->group(function () {
    // Public Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    });

    // Protected Routes
    Route::middleware('api.token')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/changepass', [AuthController::class, 'changePassword']);
        });
        Route::prefix('user')->group(function () {
            Route::get('/dashboard', [UserController::class, 'dashboard']);
            Route::get('/profile', [UserController::class, 'profile']);
            Route::get('/fees', [UserController::class, 'fees']);
            Route::get('/getfees', [UserController::class, 'getfees']);
        });
    });
});

// Health check
Route::prefix('api')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API is working',
            'timestamp' => now()->toDateTimeString(),
            'php_version' => PHP_VERSION,
        ]);
    })->name('api.ping');
});
