<?php
namespace Modules\Finance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Finance\Services\StudentFeeService;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->singleton(StudentFeeService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
