<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Api\Controller;
use Modules\Staff\Services\TeacherScope;

/**
 * أساس كونترولرات المعلم.
 *
 * context() يتحقق من (مصادقة + دور teacher + وجود ملف staff) ويرمي
 * HttpResponseException عند الفشل — أي لا يمكن نسيان الفحص (fail-closed)،
 * ولا توجد مصفوفات أخطاء يدوية. شكل الاستجابة والتسجيل (ApiLogger)
 * مطابقان تماماً للسابق عبر errorResponse().
 */
abstract class TeacherController extends Controller
{
    protected function context(Request $request): TeacherContext
    {
        $user = $request->user();

        if (! $user) {
            throw new HttpResponseException($this->errorResponse('Unauthorized', null, 401));
        }

        if ($user->role !== 'teacher') {
            throw new HttpResponseException(
                $this->errorResponse('This endpoint requires a teacher account', null, 403)
            );
        }

        $staff = TeacherScope::resolveStaff($user);

        if (! $staff) {
            throw new HttpResponseException($this->errorResponse('Staff profile not found', null, 404));
        }

        return new TeacherContext(
            user: $user,
            staff: $staff,
            sessionId: TeacherScope::currentSessionId(),
            roles: TeacherScope::staffRoles((int) $staff->id),
        );
    }
}
