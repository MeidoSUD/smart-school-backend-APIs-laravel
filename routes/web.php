<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\StudentController;
use App\Http\Controllers\Web\StaffWebController;
use App\Http\Controllers\Web\ClassController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\SubjectController;
use App\Http\Controllers\Web\ExamController;
use App\Http\Controllers\Web\HomeworkController;
use App\Http\Controllers\Web\BookController;
use App\Http\Controllers\Web\HostelController;
use App\Http\Controllers\Web\VehicleController;
use App\Http\Controllers\Web\FeeMasterController;
use App\Http\Controllers\Web\ExpenseController;
use App\Http\Controllers\Web\IncomeController;
use App\Http\Controllers\Web\RoleController;

/*
|--------------------------------------------------------------------------
| Public / Legacy Routes
|--------------------------------------------------------------------------
*/

Route::middleware(\App\Http\Middleware\SetLocale::class)->group(function () {

    Route::get('/privacy-policy', [\App\Http\Controllers\Web\PublicController::class, 'privacyPolicy'])
        ->name('privacy.policy');

    Route::middleware('guest')->group(function () {
        Route::get('/portal/login', [\App\Http\Controllers\Web\AuthWebController::class, 'showLogin'])
            ->name('web.login');

        Route::post('/portal/login', [\App\Http\Controllers\Web\AuthWebController::class, 'login'])
            ->name('web.login.post');

        Route::get('/forgot-password', [\App\Http\Controllers\Web\AuthWebController::class, 'showForgotPassword'])
            ->name('web.forgot.password');

        Route::post('/forgot-password', [\App\Http\Controllers\Web\AuthWebController::class, 'forgotPassword'])
            ->name('web.forgot.password.post');

        Route::get('/reset-password', [\App\Http\Controllers\Web\AuthWebController::class, 'showResetPassword'])
            ->name('web.reset.password');

        Route::post('/reset-password', [\App\Http\Controllers\Web\AuthWebController::class, 'resetPassword'])
            ->name('web.reset.password.post');
    });

    Route::post('/portal/logout', [\App\Http\Controllers\Web\AuthWebController::class, 'logout'])
        ->middleware('auth')
        ->name('web.logout');

    Route::get('/', fn () => redirect()->route('admin.login'));
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('students', StudentController::class);
    Route::resource('staff', StaffWebController::class);
    Route::resource('classes', ClassController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('sections', SectionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('subjects', SubjectController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('exams', ExamController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('homework', HomeworkController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('books', BookController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('hostels', HostelController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('vehicles', VehicleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('fee-masters', FeeMasterController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('incomes', IncomeController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('roles', RoleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
