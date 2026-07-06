<?php
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\AuthController;
use Modules\Core\Http\Controllers\Api\UserController;

Route::prefix('api')->group(function () {
    // Public Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login')->middleware('throttle:5,1');
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/changepass', [AuthController::class, 'changePassword'])->middleware('throttle:5,1');
        });
        Route::prefix('user')->group(function () {
            Route::match(['get', 'post'], '/choose', [UserController::class, 'choose']);
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
        ]);
    })->name('api.ping');
});
