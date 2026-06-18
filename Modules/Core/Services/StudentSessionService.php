<?php

namespace Modules\Core\Services;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Session;
use Modules\Core\Entities\User;

class StudentSessionService
{
    public function getStudentSession(User $user): ?StudentSession
    {
        $studentId = $this->resolveStudentId($user);

        if (!$studentId) {
            return null;
        }

        $currentSession = $this->getCurrentSession();

        return StudentSession::where('student_id', $studentId)
            ->when($currentSession, fn($q) => $q->where('session_id', $currentSession->id))
            ->first()
            ?? StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first()
            ?? StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
    }

    public function getStudentDefaultSession(int $studentId): ?StudentSession
    {
        $setting = \Modules\Core\Entities\Setting::where('is_active', 1)->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first()
            ?? StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first()
            ?? StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
    }

    public function getCurrentSession(): ?Session
    {
        return Session::where('is_active', 1)->first();
    }

    private function resolveStudentId(User $user): ?int
    {
        return match ($user->role) {
            'student' => $user->user_id,
            'parent' => Student::where('parent_id', $user->id)->value('id'),
            default => null,
        };
    }
}
