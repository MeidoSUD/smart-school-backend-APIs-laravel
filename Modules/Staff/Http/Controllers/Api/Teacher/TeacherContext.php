<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Modules\Core\Entities\User;
use Modules\Staff\Entities\Staff;

/**
 * سياق المعلم الموثّق: يُبنى مرة واحدة عبر TeacherController::context()
 * ويُمرَّر ككائن بدل مصفوفات [$user, $staff, $sessionId, $err].
 * sessionId غير قابل للـ null — غياب الجلسة يرمي من TeacherScope::currentSessionId().
 */
final class TeacherContext
{
    public function __construct(
        public readonly User $user,
        public readonly Staff $staff,
        public readonly int $sessionId,
        public readonly array $roles = [],
    ) {}
}
