<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\AuthWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public routes - no authentication required.
| Locale is set via ?lang= query parameter.
|
*/

Route::middleware(\App\Http\Middleware\SetLocale::class)->group(function () {

    // Privacy & Policy — fully public
    Route::get('/privacy-policy', [PublicController::class, 'privacyPolicy'])
        ->name('privacy.policy');

    // Auth pages — public (redirect if already logged in)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthWebController::class, 'showLogin'])
            ->name('web.login');

        Route::post('/login', [AuthWebController::class, 'login'])
            ->name('web.login.post');

        Route::get('/forgot-password', [AuthWebController::class, 'showForgotPassword'])
            ->name('web.forgot.password');

        Route::post('/forgot-password', [AuthWebController::class, 'forgotPassword'])
            ->name('web.forgot.password.post');

        Route::get('/reset-password', [AuthWebController::class, 'showResetPassword'])
            ->name('web.reset.password');

        Route::post('/reset-password', [AuthWebController::class, 'resetPassword'])
            ->name('web.reset.password.post');
    });

    // Logout — requires auth
    Route::post('/logout', [AuthWebController::class, 'logout'])
        ->middleware('auth')
        ->name('web.logout');

    // Default redirect to privacy policy
    Route::get('/', fn () => redirect()->route('privacy.policy'));
});
