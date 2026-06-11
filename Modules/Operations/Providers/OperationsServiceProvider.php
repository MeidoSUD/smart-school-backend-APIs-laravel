<?php
namespace Modules\Operations\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Operations\Services\NoticeNotificationService;

class OperationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->singleton(NoticeNotificationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
