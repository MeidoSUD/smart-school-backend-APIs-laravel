<?php
use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\TeacherController;

Route::middleware('api.token')->prefix('api')->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index']);
    Route::post('/teacher/rating', [TeacherController::class, 'rating']);
});
