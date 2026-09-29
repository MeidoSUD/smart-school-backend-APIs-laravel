<?php
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\AuthController;
use Modules\Core\Http\Controllers\Api\UserController;
use Modules\Academic\Http\Controllers\Api\ApplyLeaveController;

Route::prefix('api')->group(function () {
    // Public Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login')->middleware('throttle:5,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password')->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password')->middleware('throttle:5,1');
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
            // T-4.5: CI api/user/User.php fee print/collect + detail view.
            Route::post('/getProcessingfees', [UserController::class, 'getProcessingfees']);
            Route::post('/getcollectfee', [UserController::class, 'getcollectfee']);
            Route::post('/printFeesByGroupArray', [UserController::class, 'printFeesByGroupArray']);
            Route::get('/view/{id}', [UserController::class, 'view']);

            Route::get('/documents', [UserController::class, 'documents']);
            Route::post('/documents', [UserController::class, 'adddoc']);
            Route::get('/documents/download/{id}', [UserController::class, 'downloaddoc']);

            Route::post('/changeusername', [UserController::class, 'changeusername']);
            Route::post('/language', [UserController::class, 'language']);
            Route::post('/currency', [UserController::class, 'currency']);

            Route::get('/apply_leave', [ApplyLeaveController::class, 'index']);
            Route::match(['get', 'post'], '/apply_leave/get_details/{id}', [ApplyLeaveController::class, 'get_details']);
            Route::get('/apply_leave/download/{id}', [ApplyLeaveController::class, 'download']);
            Route::post('/apply_leave/add', [ApplyLeaveController::class, 'add']);
            Route::match(['get', 'post', 'delete'], '/apply_leave/remove_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);
            Route::get('/apply_leave/{id}', [ApplyLeaveController::class, 'get_details']);
            Route::delete('/apply_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);
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
