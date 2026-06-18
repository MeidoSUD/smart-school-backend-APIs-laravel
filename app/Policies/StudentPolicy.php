<?php

namespace App\Policies;

use Modules\Core\Entities\User;
use Modules\Academic\Entities\Student;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return match ($user->role) {
            'student' => $user->user_id === $student->id,
            'parent' => $student->parent_id === $user->id,
            'teacher', 'staff', 'admin' => true,
            default => false,
        };
    }

    public function update(User $user, Student $student): bool
    {
        return in_array($user->role, ['admin', 'staff', 'teacher']);
    }
}
