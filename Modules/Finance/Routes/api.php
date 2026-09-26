<?php
use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\Api\OfflinePaymentController;
use Modules\Finance\Http\Controllers\Api\StudentfeeController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/offlinepayment', [OfflinePaymentController::class, 'index']);
    Route::post('/offlinepayment/add', [OfflinePaymentController::class, 'add']);

    // T-4.6: read-only port of CI api/user/Studentfee.php (view + searchpayment).
    Route::get('/studentfee/view/{id}', [StudentfeeController::class, 'view']);
    Route::match(['get', 'post'], '/studentfee/searchpayment', [StudentfeeController::class, 'searchpayment']);
});
