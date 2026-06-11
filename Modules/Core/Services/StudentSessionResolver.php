<?php

namespace Modules\Core\Services;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Session;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;

class StudentSessionResolver
{
    public function currentSessionId(): ?int
    {
        $setting = Setting::first();

        if ($setting && $setting->session_id) {
            return (int) $setting->session_id;
        }

        $activeSession = Session::where('is_active', 'yes')->first();

        return $activeSession ? (int) $activeSession->id : null;
    }

    public function resolveStudentId(User $user, ?int $studentId = null): ?int
    {
        if ($user->role === 'student') {
            return $user->user_id ? (int) $user->user_id : null;
        }

        if ($user->role === 'parent') {
            if ($studentId) {
                $child = Student::where('id', $studentId)
                    ->where('parent_id', $user->id)
                    ->first();

                return $child ? (int) $child->id : null;
            }

            $firstChild = Student::where('parent_id', $user->id)->first();

            return $firstChild ? (int) $firstChild->id : null;
        }

        return null;
    }

    public function resolveSession(User $user, ?int $studentId = null): ?StudentSession
    {
        $resolvedStudentId = $this->resolveStudentId($user, $studentId);

        if (!$resolvedStudentId) {
            return null;
        }

        return $this->resolveSessionForStudent($resolvedStudentId);
    }

    public function resolveSessionForStudent(int $studentId): ?StudentSession
    {
        $sessionId = $this->currentSessionId();

        if ($sessionId) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->first();

            if ($studentSession) {
                return $studentSession;
            }
        }

        $studentSession = StudentSession::where('student_id', $studentId)
            ->where('default_login', 1)
            ->first();

        if ($studentSession) {
            return $studentSession;
        }

        return StudentSession::where('student_id', $studentId)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parentChildrenWithSessions(User $user): array
    {
        if ($user->role !== 'parent') {
            return [];
        }

        $children = Student::where('parent_id', $user->id)->get();
        $result = [];

        foreach ($children as $child) {
            $session = $this->resolveSessionForStudent((int) $child->id);
            $result[] = [
                'student_id' => $child->id,
                'firstname' => $child->firstname,
                'lastname' => $child->lastname,
                'student_session_id' => $session?->id,
                'class_id' => $session?->class_id,
                'section_id' => $session?->section_id,
            ];
        }

        return $result;
    }
}
