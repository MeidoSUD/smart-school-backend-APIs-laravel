<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \Modules\Academic\Entities\Student::class => \App\Policies\StudentPolicy::class,
        \Modules\Finance\Entities\StudentFee::class => \App\Policies\FeePolicy::class,
        \Modules\Academic\Entities\Exam::class => \App\Policies\ExamPolicy::class,
        \Modules\Core\Entities\User::class => \App\Policies\UserPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('staff', fn($user) => in_array($user->role, ['admin', 'staff', 'teacher']));
        Gate::define('student-or-parent', fn($user) => in_array($user->role, ['student', 'parent']));

        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::define('permission', fn($user, string $permission) => $user->hasPermission($permission));
    }
}
