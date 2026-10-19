<?php
use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\TeacherController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index']);
    Route::post('/teacher/rating', [TeacherController::class, 'rating']);
    Route::post('/teacher/getSubjctByClassandSection', [TeacherController::class, 'getSubjctByClassandSection']);
    Route::post('/teacher/getSubjectTeachers', [TeacherController::class, 'getSubjectTeachers']);
    // G-1.10: registered last so the wildcard does not swallow fixed segments.
    // P0: numeric constraint so teacher.php fixed segments (/me, /dashboard,
    // /my-classes) registered afterwards are never swallowed by {id}.
    Route::get('/teacher/{id}', [TeacherController::class, 'view'])->whereNumber('id');
});
