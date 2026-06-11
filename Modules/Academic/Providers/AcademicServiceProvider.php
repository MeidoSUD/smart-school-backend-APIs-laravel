<?php
namespace Modules\Academic\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Academic\Services\AttendanceCalculator;
use Modules\Academic\Services\DashboardService;

class AcademicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->singleton(AttendanceCalculator::class);
        $this->app->singleton(DashboardService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
