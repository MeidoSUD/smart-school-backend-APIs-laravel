<?php

namespace Modules\Core\Services;

use Modules\Core\Entities\User;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Modules\Staff\Entities\Staff;
use Modules\Finance\Entities\StudentFeeMaster;

class UserResponseService
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {}

    public function buildUserResponse(User $user): array
    {
        $data = $user->toArray();
        unset($data['password']);

        switch ($user->role) {
            case 'student':
                $data = $this->buildStudentResponse($user, $data);
                break;

            case 'parent':
                $data = $this->buildParentResponse($user, $data);
                break;

            case 'teacher':
            case 'staff':
            case 'accountant':
            case 'librarian':
                $data = $this->buildStaffResponse($user, $data);
                break;
        }

        unset($data['password']);
        return $data;
    }

    private function buildStudentResponse(User $user, array $data): array
    {
        $student = Student::with('studentSessions.class', 'studentSessions.section')
            ->find($user->user_id);

        if ($student) {
            $data = array_merge($data, $student->toArray());

            $studentSession = $this->studentSessionService->getStudentDefaultSession($student->id);
            if ($studentSession) {
                $data['class_id'] = $studentSession->class_id;
                $data['section_id'] = $studentSession->section_id;
                $data['student_session_id'] = $studentSession->id;
                $data['class'] = $studentSession->class->class ?? null;
                $data['section'] = $studentSession->section->section ?? null;
                $data['fees'] = $this->getStudentFeeSummary($studentSession->id);
            }
        }

        return $data;
    }

    private function buildParentResponse(User $user, array $data): array
    {
        $child = Student::with('studentSessions.class', 'studentSessions.section')
            ->where('parent_id', $user->id)
            ->first();

        if ($child) {
            $studentSession = $this->studentSessionService->getStudentDefaultSession($child->id);
            if ($studentSession) {
                $data['student_id'] = $child->id;
                $data['class_id'] = $studentSession->class_id;
                $data['section_id'] = $studentSession->section_id;
                $data['student_session_id'] = $studentSession->id;
                $data['class'] = $studentSession->class->class ?? null;
                $data['section'] = $studentSession->section->section ?? null;
                $data['fees'] = $this->getStudentFeeSummary($studentSession->id);
            }
        }

        $data['childs'] = json_decode($user->childs, true) ?? [];
        return $data;
    }

    private function buildStaffResponse(User $user, array $data): array
    {
        $staff = Staff::where('user_id', $user->id)->first();
        if ($staff) {
            $data = array_merge($data, $staff->toArray());
        }
        return $data;
    }

    private function getStudentFeeSummary(int $studentSessionId): array
    {
        $fees = StudentFeeMaster::where('student_session_id', $studentSessionId)->get();

        return [
            'total_fees' => $fees->sum('amount'),
            'fees_list' => $fees->toArray(),
        ];
    }
}
