<?php

namespace App\Policies;

use Modules\Core\Entities\User;

class UserPolicy
{
    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id || in_array($user->role, ['admin', 'staff']);
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    public function delete(User $user, User $target): bool
    {
        return $user->role === 'admin' && $user->id !== $target->id;
    }
}
