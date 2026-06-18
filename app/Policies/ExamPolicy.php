<?php

namespace App\Policies;

use Modules\Core\Entities\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'parent', 'teacher', 'staff', 'admin']);
    }

    public function view(User $user, $exam): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher', 'staff']);
    }

    public function update(User $user, $exam): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function delete(User $user, $exam): bool
    {
        return $user->role === 'admin';
    }
}
