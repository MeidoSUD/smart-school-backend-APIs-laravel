<?php

declare(strict_types=1);

namespace Modules\Staff\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;
use Modules\Staff\Entities\Staff;
use RuntimeException;

/**
 * TeacherScope — المركز الوحيد لفلترة نطاق المعلم.
 *
 * المرجع: CodeIgniter (المصدر الوحيد للحقيقة) —
 * - Setting_model::getCurrentSession(): الجلسة = sch_settings.session_id من أول صف
 *   مرتباً بـ id (بدون فلترة is_active)، و 0 عند غياب الجدول.
 * - Staffroles_model::getStaffRoles(): أدوار الموظف (inner join مع roles)؛
 *   و Staff_model::checkLogin() يعتمد الدور الأول $roles[0] كأساسي.
 * - Classteacher_model::teacherByClassSection(): رائد الفصل مُفلتر بالجلسة.
 * - Teachersubject_model / Subjecttimetable_model: جداول مُفلترة بالجلسة
 *   (انظر Subjecttimetable_model: كل الاستعلامات بـ session_id).
 *
 * تقوية مقصودة (hardening) عن CI — موثقة لا مدّعاة كـ parity:
 * - CI يقيّد فعلياً عبر Customlib::get_myClassSection() (role_id=2 + علم
 *   sch_settings.class_teacher العام → كل الفصول، وإلا بدون تقييد)، أما
 *   Customlib::getClassbyteacher() فكود ميت بلا منادين ومعطوب
 *   (Classteacher_model::classbysubjectteacher لا تُرجع شيئاً).
 *   هنا نُبقي تقييداً لكل معلم (فصوله فقط) — أضيق من CI عمداً (حماية)،
 *   واختبارات الوحدة تعتمد 403 خارج النطاق.
 * - orderBy(staff_roles.id): تثبيت حتمي لـ $roles[0] (في CI الترتيب غير مضمون).
 * - قراءة الجلسة طازجة كل مرة بلا كاش (مثل CI) — جلسة قديمة = نطاق خاطئ.
 *
 * صارم (fail-closed): لا قيم وهمية. المدخلات غير الصالحة ترمي
 * InvalidArgumentException، وغياب الجلسة يرمي RuntimeException
 * (CI يُرجع 0؛ هنا نرمي لمنع تسرب نطاق فارغ/خاطئ بصمت).
 */
final class TeacherScope
{
    public const TEACHER_ROLE_ID = 2;

    /**
     * ملاحظة: فحص الدور/المصادقة يتم عبر TeacherController::context()
     * (يرمي HttpResponseException — fail-closed) بدل إرجاع رسائل نصية.
     */

    /**
     * users.user_id = staff.id للمعلم (انظر UserSeeder؛ وعلى نمط CI:
     * Teacher_model يربط users.user_id = teachers.id مع role=teacher).
     * يُرجع null فقط عند عدم وجود ملف staff مرتبط — وليس عند مدخلات فاسدة.
     *
     * @throws InvalidArgumentException عندما يكون User غير محفوظ (بدون id موجب).
     */
    public static function resolveStaff(User $user): ?Staff
    {
        if ((int) ($user->id ?? 0) <= 0) {
            throw new InvalidArgumentException('User must be persisted before resolving staff.');
        }

        // الاتجاه الأساسي: users.user_id → staff.id
        $legacyStaffId = (int) ($user->user_id ?? 0);
        if ($legacyStaffId > 0) {
            return Staff::where('id', $legacyStaffId)->first();
        }

        // الاتجاه العكسي فقط عند غياب الرابط الأساسي: staff.user_id → users.id
        return Staff::where('user_id', (int) $user->id)->first();
    }

    /**
     * أدوار الموظف — الأول = الأساسي مثل Staff_model::checkLogin().
     * (تثبيت حتمي بـ orderBy(id)؛ في CI الترتيب غير مضمون.)
     *
     * @return list<object{role_id: int, name: string}>
     *
     * @throws InvalidArgumentException عندما يكون staffId غير موجب.
     */
    public static function staffRoles(int $staffId): array
    {
        self::assertPositiveId($staffId, 'staffId');

        return DB::table('staff_roles')
            ->join('roles', 'roles.id', '=', 'staff_roles.role_id')
            ->where('staff_roles.staff_id', $staffId)
            ->orderBy('staff_roles.id')
            ->select('staff_roles.role_id', 'roles.name')
            ->get()
            ->all();
    }

    /**
     * الجلسة الحالية = sch_settings.session_id من أول صف مرتباً بـ id —
     * مثل Setting_model::getCurrentSession() تماماً (بلا فلترة is_active،
     * وبلا كاش: قراءة طازجة كل مرة مثل CI).
     *
     * @throws RuntimeException عند غياب جلسة صالحة — fail-closed، لا null وهمي
     * (CI يُرجع 0 في هذه الحالة؛ الرمي أضيق عمداً).
     */
    public static function currentSessionId(): int
    {
        // أول صف بـ id — يطابق Setting_model::get() + getCurrentSession().
        // ملاحظة: قيمة is_active تختلف بين نسخ CI (عدد 1) و Laravel ('yes')،
        // لذا لا فلترة عليها — مثل CI.
        $sessionId = (int) (Setting::orderBy('id')->value('session_id') ?? 0);

        if ($sessionId <= 0) {
            throw new RuntimeException('No active school session configured.');
        }

        return $sessionId;
    }

    /**
     * فصول المعلم الحالية مع أسمائها وعلامة رائد الفصل.
     * Union مُفلتر بالجلسة من: class_teacher (مثل teacherByClassSection)
     * ∪ teacher_subjects ∪ subject_timetable (مثل Subjecttimetable_model).
     *
     * @return list<array{class_section_id: int, class_id: int, section_id: int, class_name: ?string, section_name: ?string, is_class_teacher: int}>
     *
     * @throws InvalidArgumentException عندما تكون المدخلات غير موجبة.
     */
    public static function myClasses(int $staffId, int $sessionId): array
    {
        self::assertPositiveId($staffId, 'staffId');
        self::assertPositiveId($sessionId, 'sessionId');

        $classSectionIds = self::classSectionIds($staffId, $sessionId);

        if ($classSectionIds === []) {
            return [];
        }

        $rows = DB::table('class_sections')
            ->leftJoin('classes', 'classes.id', '=', 'class_sections.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'class_sections.section_id')
            ->whereIn('class_sections.id', $classSectionIds)
            ->orderBy('class_sections.class_id')
            ->orderBy('class_sections.section_id')
            ->select(
                'class_sections.id as class_section_id',
                'class_sections.class_id',
                'class_sections.section_id',
                'classes.class as class_name',
                'sections.section as section_name'
            )
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $classTeacherPairs = DB::table('class_teacher')
            ->where('staff_id', $staffId)
            ->where('session_id', $sessionId)
            ->get(['class_id', 'section_id']);

        return $rows->map(static function ($row) use ($classTeacherPairs): array {
            $isClassTeacher = $classTeacherPairs->contains(
                static fn ($ct): bool => (int) $ct->class_id === (int) $row->class_id
                    && (int) $ct->section_id === (int) $row->section_id
            );

            return [
                'class_section_id' => (int) $row->class_section_id,
                'class_id' => (int) $row->class_id,
                'section_id' => (int) $row->section_id,
                'class_name' => $row->class_name,
                'section_name' => $row->section_name,
                'is_class_teacher' => $isClassTeacher ? 1 : 0,
            ];
        })->all();
    }

    /**
     * هل class_section ضمن نطاق المعلم؟
     * فحص وجود مباشر (3× EXISTS بقصر الدائرة) بدل تحميل كل الفصول.
     *
     * @throws InvalidArgumentException عندما تكون أي من المدخلات غير موجبة.
     */
    public static function inScope(int $staffId, int $sessionId, int $classSectionId): bool
    {
        self::assertPositiveId($staffId, 'staffId');
        self::assertPositiveId($sessionId, 'sessionId');
        self::assertPositiveId($classSectionId, 'classSectionId');

        // 1) مدرس مادة: فحص مباشر على class_section_id.
        $viaSubject = DB::table('teacher_subjects')
            ->where('teacher_id', $staffId)
            ->where('session_id', $sessionId)
            ->where('class_section_id', $classSectionId)
            ->exists();

        if ($viaSubject) {
            return true;
        }

        // 2) و 3) يتطلبان class_id/section_id — صف غير موجود = خارج النطاق (false، ليس استثناءً).
        $section = DB::table('class_sections')
            ->where('id', $classSectionId)
            ->first(['class_id', 'section_id']);

        if ($section === null || (int) ($section->class_id ?? 0) <= 0 || (int) ($section->section_id ?? 0) <= 0) {
            return false;
        }

        $classId = (int) $section->class_id;
        $sectionId = (int) $section->section_id;

        // 2) رائد فصل.
        $viaClassTeacher = DB::table('class_teacher')
            ->where('staff_id', $staffId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();

        if ($viaClassTeacher) {
            return true;
        }

        // 3) جدول حصص.
        return DB::table('subject_timetable')
            ->where('staff_id', $staffId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
    }

    /**
     * @return list<int>
     */
    private static function classSectionIds(int $staffId, int $sessionId): array
    {
        $fromClassTeacher = DB::table('class_teacher')
            ->join('class_sections', static function ($join): void {
                $join->on('class_sections.class_id', '=', 'class_teacher.class_id')
                    ->on('class_sections.section_id', '=', 'class_teacher.section_id');
            })
            ->where('class_teacher.staff_id', $staffId)
            ->where('class_teacher.session_id', $sessionId)
            ->pluck('class_sections.id');

        $fromSubjects = DB::table('teacher_subjects')
            ->where('teacher_id', $staffId)
            ->where('session_id', $sessionId)
            ->pluck('class_section_id');

        $fromTimetable = DB::table('subject_timetable')
            ->join('class_sections', static function ($join): void {
                $join->on('class_sections.class_id', '=', 'subject_timetable.class_id')
                    ->on('class_sections.section_id', '=', 'subject_timetable.section_id');
            })
            ->where('subject_timetable.staff_id', $staffId)
            ->where('subject_timetable.session_id', $sessionId)
            ->pluck('class_sections.id');

        return $fromClassTeacher
            ->merge($fromSubjects)
            ->merge($fromTimetable)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public static function subjectIds(int $staffId, int $sessionId): array
    {
        self::assertPositiveId($staffId, 'staffId');
        self::assertPositiveId($sessionId, 'sessionId');

        $fromTimetable = DB::table('subject_timetable')
            ->join('subject_group_subjects', 'subject_group_subjects.id', '=', 'subject_timetable.subject_group_subject_id')
            ->where('subject_timetable.staff_id', $staffId)
            ->where('subject_timetable.session_id', $sessionId)
            ->pluck('subject_group_subjects.subject_id');

        $fromClassTeacher = DB::table('class_teacher')
            ->join('subject_timetable', static function ($join): void {
                $join->on('subject_timetable.class_id', '=', 'class_teacher.class_id')
                    ->on('subject_timetable.section_id', '=', 'class_teacher.section_id')
                    ->on('subject_timetable.session_id', '=', 'class_teacher.session_id');
            })
            ->join('subject_group_subjects', 'subject_group_subjects.id', '=', 'subject_timetable.subject_group_subject_id')
            ->where('class_teacher.staff_id', $staffId)
            ->where('class_teacher.session_id', $sessionId)
            ->pluck('subject_group_subjects.subject_id');

        $fromTeacherSubjects = DB::table('teacher_subjects')
            ->where('teacher_id', $staffId)
            ->where('session_id', $sessionId)
            ->pluck('subject_id');

        return $fromTimetable
            ->merge($fromClassTeacher)
            ->merge($fromTeacherSubjects)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private static function assertPositiveId(int $id, string $name): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("{$name} must be a positive integer.");
        }
    }
}
