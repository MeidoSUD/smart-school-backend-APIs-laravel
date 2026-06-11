<?php

namespace Modules\Core\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Services\StudentSessionResolver;

trait ResolvesStudentContext
{
    protected function studentIdFromRequest(Request $request): ?int
    {
        $studentId = $request->input('student_id');

        return $studentId !== null && $studentId !== '' ? (int) $studentId : null;
    }

    protected function studentSession(Request $request): ?StudentSession
    {
        return app(StudentSessionResolver::class)->resolveSession(
            $request->user(),
            $this->studentIdFromRequest($request)
        );
    }

    protected function resolvedStudentId(Request $request): ?int
    {
        return app(StudentSessionResolver::class)->resolveStudentId(
            $request->user(),
            $this->studentIdFromRequest($request)
        );
    }
}
