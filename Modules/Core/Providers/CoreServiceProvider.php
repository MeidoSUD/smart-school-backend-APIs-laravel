<?php
namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Artisan::command('auth:hash-plaintext-passwords', function () {
            $this->call(\Modules\Core\Console\Commands\HashPlaintextPasswords::class);
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
