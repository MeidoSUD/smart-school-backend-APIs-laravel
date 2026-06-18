<?php

namespace App\Policies;

use Modules\Core\Entities\User;

class FeePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['student', 'parent', 'admin', 'accountant']);
    }

    public function view(User $user, $fee): bool
    {
        return match ($user->role) {
            'student', 'parent' => $this->ownsFee($user, $fee),
            'admin', 'accountant' => true,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'accountant']);
    }

    private function ownsFee(User $user, $fee): bool
    {
        return $fee->student_session_id === $user->user_id
            || $fee->student->parent_id === $user->id;
    }
}
