<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * تواصل المعلم — parity مع:
 * - CI notice_board (لوحة الإعلانات: send_notification المرئية للموظف + دور 2، مع حالة القراءة)
 * - CI staff directory (دليل الموظفين: staff:can_view ممنوحة للدور 2)
 * - CI mailsms compose (إنشاء إشعار لفصوله — SMS/البريد الفعلي عبر البوابات غير مُنفذ، يُوثق كفرق)
 * - CI email_sms_log (سجل ما أرسله)
 */
class TeacherCommunicateController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherCommunicateController');
    }

    public function noticeBoard(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $rows = DB::table('send_notification')
            ->leftJoin('staff as creator', 'creator.id', '=', 'send_notification.created_id')
            ->leftJoin('read_notification', function ($j) use ($ctx) {
                $j->on('read_notification.notification_id', '=', 'send_notification.id')
                    ->where('read_notification.staff_id', $ctx->staff->id);
            })
            ->where('send_notification.visible_staff', 'yes')
            ->where('send_notification.is_active', 'yes')
            ->where('send_notification.created_id', '!=', $ctx->staff->id)
            ->select('send_notification.id', 'send_notification.title', 'send_notification.message',
                'send_notification.publish_date', 'send_notification.date',
                'creator.name as created_by_name',
                DB::raw('CASE WHEN read_notification.id IS NULL THEN 0 ELSE 1 END as is_read'))
            ->orderByDesc('send_notification.id')->paginate(25);

        return $this->successResponse(['notifications' => $rows]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $exists = DB::table('send_notification')->where('id', $id)->exists();
        if (! $exists) return $this->errorResponse('Notification not found', null, 404);

        DB::table('read_notification')->updateOrInsert(
            ['notification_id' => $id, 'staff_id' => $ctx->staff->id],
            ['created_at' => now()]
        );

        return $this->successResponse(null, 'Marked as read');
    }

    public function staffDirectory(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['search' => 'nullable|string|max:100', 'role_id' => 'nullable|integer']);

        $query = DB::table('staff')
            ->leftJoin('staff_roles', 'staff_roles.staff_id', '=', 'staff.id')
            ->leftJoin('roles', 'roles.id', '=', 'staff_roles.role_id')
            ->where('staff.is_active', '1')
            ->select('staff.id', 'staff.employee_id', 'staff.name', 'staff.surname',
                'staff.email', 'staff.contact_no', 'staff.image', 'staff.gender',
                'roles.name as role_name');

        if (! empty($v['search'])) {
            $s = '%' . $v['search'] . '%';
            $query->where(fn ($q) => $q->where('staff.name', 'like', $s)->orWhere('staff.employee_id', 'like', $s));
        }
        if (! empty($v['role_id'])) $query->where('staff_roles.role_id', $v['role_id']);

        return $this->successResponse(['staff' => $query->paginate(25)]);
    }

    /** إنشاء إشعار لفصول المعلم (يقابل compose — التسليم الفعلي SMS/بريد خارج النطاق) */
    public function sendNotice(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'title' => 'required|string|max:255', 'message' => 'required|string',
            'publish_date' => 'nullable|date',
            'visible_student' => 'nullable|boolean', 'visible_parent' => 'nullable|boolean',
        ]);

        $id = DB::table('send_notification')->insertGetId([
            'title' => $v['title'], 'message' => $v['message'],
            'publish_date' => $v['publish_date'] ?? date('Y-m-d'), 'date' => date('Y-m-d'),
            'visible_student' => ! empty($v['visible_student']) ? 'yes' : 'no',
            'visible_staff' => 'no', 'visible_parent' => ! empty($v['visible_parent']) ? 'yes' : 'no',
            'created_by' => $ctx->staff->id, 'created_id' => $ctx->staff->id,
            'is_active' => 'yes', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Notice created successfully. SMS/email gateway delivery is not included.');
    }

    public function sentLog(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $rows = DB::table('send_notification')->where('created_id', $ctx->staff->id)
            ->orderByDesc('id')->paginate(25);

        return $this->successResponse(['sent' => $rows]);
    }

    public function emailTemplates(Request $request): JsonResponse
    {
        $this->context($request);

        $templates = DB::table('email_template')->orderBy('id')->get();

        return $this->successResponse(['templates' => $templates]);
    }

    public function smsTemplates(Request $request): JsonResponse
    {
        $this->context($request);

        $templates = DB::table('sms_template')->orderBy('id')->get();

        return $this->successResponse(['templates' => $templates]);
    }

    public function sendEmail(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'email', 'can_view')) {
            return $this->errorResponse('Forbidden: missing email can_view permission', null, 403);
        }

        $v = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'email_template_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $scopeCheck = $this->targetClassScope($ctx, $v);
        if ($scopeCheck !== null) {
            return $scopeCheck;
        }

        $id = DB::table('messages')->insertGetId([
            'title' => $v['title'],
            'message' => $v['message'],
            'email_template_id' => $v['email_template_id'] ?? null,
            'send_through' => 'mail',
            'send_mail' => 1,
            'send_sms' => 0,
            'is_group' => empty($v['class_id']) ? 1 : 0,
            'is_individual' => 0,
            'is_class' => ! empty($v['class_id']) ? 1 : 0,
            'is_schedule' => 0,
            'sent' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Email message queued successfully');
    }

    public function sendSms(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'sms', 'can_view')) {
            return $this->errorResponse('Forbidden: missing sms can_view permission', null, 403);
        }

        $v = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'sms_template_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $scopeCheck = $this->targetClassScope($ctx, $v);
        if ($scopeCheck !== null) {
            return $scopeCheck;
        }

        $id = DB::table('messages')->insertGetId([
            'title' => $v['title'],
            'message' => $v['message'],
            'sms_template_id' => $v['sms_template_id'] ?? null,
            'send_through' => 'sms',
            'send_mail' => 0,
            'send_sms' => 1,
            'is_group' => empty($v['class_id']) ? 1 : 0,
            'is_individual' => 0,
            'is_class' => ! empty($v['class_id']) ? 1 : 0,
            'is_schedule' => 0,
            'sent' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'SMS message queued successfully');
    }

    /**
     * التحقق أن الفصل المستهدف (عند تحديده) ضمن نطاق المعلم.
     * يعيد null عند التجاوز، أو استجابة 403/422 عند المخالفة.
     */
    private function targetClassScope(TeacherContext $ctx, array $v): ?JsonResponse
    {
        if (empty($v['class_id']) && empty($v['section_id'])) {
            return null;
        }
        if (empty($v['class_id']) || empty($v['section_id'])) {
            return $this->errorResponse('Both class_id and section_id are required for class targeting', null, 422);
        }
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Target class section is not in your scope', null, 403);
        }

        return null;
    }
}

