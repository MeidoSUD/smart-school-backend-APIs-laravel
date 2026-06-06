<?php
use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\Api\OfflinePaymentController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/offlinepayment', [OfflinePaymentController::class, 'index']);
    Route::post('/offlinepayment/add', [OfflinePaymentController::class, 'add']);
});
