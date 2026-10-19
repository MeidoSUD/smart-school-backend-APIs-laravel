<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;

/**
 * تقويم ومهام المعلم — parity مع CI admin/Calendar:
 * - events/getevents/saveevent/updateevent/delete_event/view_event (event_type=public/private/sameforall/protected)
 * - addtodo/gettaskbyid/markcomplete (event_type=task, event_for=staff_id + role_id)
 * جدول واحد events في CI — نفس الشيء هنا.
 *
 * ملاحظات التوافق:
 * - CI يقبل أسماء حقول الويب (title/event_from/event_to/description/eventcolor/eventtype/task_title/task_date/eventid)
 *   و Laravel يقبلها كأسماء بديلة بجانب أسماء API (event_title/start_date/...).
 * - CI getevents يُرجع مصفوفة fullCalendar مُفلترة بالرؤية؛ events() تُرجع نفس التصفية مع pagination للتوافق.
 * - التواريخ تُقبل بصيغ متعددة (Y-m-d H:i:s، school format، date فقط) عبر strtotime.
 */
class TeacherCalendarController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherCalendarController');
    }

    /** مفاتيح الرؤية مثل CI: userdata[id]=staff.id و role_id الأساسي */
    private function visibilityKeys($ctx): array
    {
        $roleId = isset($ctx->roles[0]->role_id) ? (int) $ctx->roles[0]->role_id : 2;

        return [(string) $ctx->staff->id, $roleId];
    }

    private function requirePrivilege(int $staffId, string $perm): ?JsonResponse
    {
        if (! TeacherRbac::hasPrivilege($staffId, 'calendar_to_do_list', $perm)) {
            return $this->errorResponse('Forbidden', null, 403);
        }

        return null;
    }

    /** تصفية الرؤية مطابقة لـ CI Calendar::getevents() — مطابقة صارمة (strict):
     *  task تُرى فقط عند (event_for=user و role_id=role) مثل CI تماماً
     *  (PHP: NULL==role → false، و SQL: NULL=role → excluded).
     *  المهام القديمة بـ role_id=NULL أُصلحت بترحيل بيانات (backfill) لا بتوسيع الاستعلام. */
    private function applyVisibility($query, string $userId, int $roleId)
    {
        // public (event_for=0) + protected (للجميع) + private (event_for=user) + sameforall (event_for=role) + task (user+role)
        return $query->where(function ($q) use ($userId, $roleId) {
            $q->where(function ($qq) use ($userId) {
                $qq->where('event_type', 'private')->where('event_for', $userId);
            })->orWhere(function ($qq) use ($roleId) {
                $qq->where('event_type', 'sameforall')->where('event_for', (string) $roleId);
            })->orWhere(function ($qq) {
                $qq->where('event_type', 'public')->where('event_for', '0');
            })->orWhere(function ($qq) {
                $qq->where('event_type', 'protected');
            })->orWhere(function ($qq) use ($userId, $roleId) {
                $qq->where('event_type', 'task')->where('event_for', $userId)->where('role_id', $roleId);
            });
        });
    }

    private function toFullCalendar(object $row): array
    {
        return [
            'title' => $row->event_title,
            'start' => $row->start_date,
            'end' => $row->end_date,
            'description' => $row->event_description,
            'id' => $row->id,
            'backgroundColor' => $row->event_color,
            'borderColor' => $row->event_color,
            'event_type' => $row->event_type,
        ];
    }

    private function parseDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $ts = strtotime(trim($value));
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }

    /** حساب event_for مثل CI saveevent/updateevent */
    private function resolveEventFor(string $eventType, string $userId, int $roleId): string
    {
        return match ($eventType) {
            'private' => $userId,
            'sameforall', 'protected' => (string) $roleId,
            'public' => '0',
            default => $userId,
        };
    }

    /** فلتر التداخل الشهري: CI getevents بلا فلترة (fullCalendar يعرض بالتداخل) */
    private function applyOverlap($query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->where('end_date', '>=', date('Y-m-d 00:00:00', strtotime($from)));
        }
        if ($to) {
            $query->where('start_date', '<=', date('Y-m-d 23:59:59', strtotime($to)));
        }
    }

    public function events(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);
        // fullCalendar يرسل start/end؛ API القديم يقبل from/to — ندعم الاسمين
        $v = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        $from = $v['from'] ?? $v['start'] ?? null;
        $to = $v['to'] ?? $v['end'] ?? null;

        $query = DB::table('events')->orderBy('start_date');
        $this->applyVisibility($query, $userId, $roleId);
        $this->applyOverlap($query, $from, $to);

        // بمدى شهري → مصفوفة fullCalendar كاملة مثل CI getevents (بلا pagination)
        if ($from || $to) {
            $rows = $query->get();

            return $this->successResponse([
                'events' => $rows->map(fn ($r) => $this->toFullCalendar($r))->all(),
                'total' => $rows->count(),
            ]);
        }

        $page = $query->paginate(50);
        $page->getCollection()->transform(fn ($r) => $this->toFullCalendar($r));

        return $this->successResponse(['events' => $page]);
    }

    /** parity مباشر مع CI getevents: مصفوفة fullCalendar بدون pagination */
    public function feed(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);
        $v = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);
        $from = $v['from'] ?? $v['start'] ?? null;
        $to = $v['to'] ?? $v['end'] ?? null;

        $query = DB::table('events')->orderBy('start_date');
        $this->applyVisibility($query, $userId, $roleId);
        $this->applyOverlap($query, $from, $to);
        $rows = $query->get();

        return $this->successResponse([
            'events' => $rows->map(fn ($r) => $this->toFullCalendar($r))->all(),
            'total' => $rows->count(),
        ]);
    }

    public function viewEvent(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        $row = DB::table('events')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Event not found', null, 404);
        }

        // حقول CI view_event: colorid + startdate/enddate بصيغة المدرسة (هنا Y-m-d H:i:s)
        $event = (array) $row;
        $event['colorid'] = ltrim((string) ($row->event_color ?? ''), '#');
        $event['startdate'] = $row->start_date;
        $event['enddate'] = $row->end_date;

        return $this->successResponse(['event' => $event]);
    }

    public function getTaskById(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_edit')) {
            return $deny;
        }
        $row = DB::table('events')->where('id', $id)->where('event_type', 'task')->first();
        if (! $row) {
            return $this->errorResponse('Task not found', null, 404);
        }

        return $this->successResponse(['task' => $row]);
    }

    public function saveEvent(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        // CI saveevent بلا فحص خاص (صفحة events تتطلب can_view) — نطلب can_view كحد أدنى (hardening موثق)
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);

        // أسماء CI البديلة: title/event_from/event_to/description/eventcolor/event_type
        $title = $request->input('event_title', $request->input('title'));
        $from = $request->input('start_date', $request->input('event_from'));
        $to = $request->input('end_date', $request->input('event_to'));
        $description = $request->input('event_description', $request->input('description', ''));
        $eventType = $request->input('event_type', 'public');
        $eventColor = $request->input('event_color', $request->input('eventcolor', '#337ab7')) ?: '#337ab7';

        $data = [
            'event_title' => $title,
            'start_date' => $from,
            'end_date' => $to,
            'event_type' => $eventType,
        ];
        $validator = validator($data, [
            'event_title' => 'required|string|max:255',
            'start_date' => 'required',
            'end_date' => 'required',
            'event_type' => 'required|in:public,private,sameforall,protected',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->toArray(), 422);
        }
        $start = $this->parseDateTime((string) $from);
        $end = $this->parseDateTime((string) $to);
        if (! $start || ! $end) {
            return $this->errorResponse('Validation failed', ['start_date' => ['Invalid date format']], 422);
        }

        $id = DB::table('events')->insertGetId([
            'event_title' => trim((string) $title),
            'event_description' => (string) $description,
            'start_date' => $start,
            'end_date' => $end,
            'event_type' => $eventType,
            'event_color' => $eventColor,
            'event_for' => $this->resolveEventFor($eventType, $userId, $roleId),
            'status' => 'no',
            'is_active' => 'yes',
        ]);

        return $this->successResponse(['id' => $id], 'Event created successfully');
    }

    public function updateEvent(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_edit')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);
        $row = DB::table('events')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Event not found', null, 404);
        }

        // CI updateevent: title/event_from/event_to/description/eventtype/eventcolor/eventid
        $title = $request->input('event_title', $request->input('title', $row->event_title));
        $from = $request->input('start_date', $request->input('event_from', $row->start_date));
        $to = $request->input('end_date', $request->input('event_to', $row->end_date));
        $description = $request->input('event_description', $request->input('description', $row->event_description));
        $eventType = $request->input('event_type', $request->input('eventtype', $row->event_type));
        $eventColor = $request->input('event_color', $request->input('eventcolor', $row->event_color));

        if (! in_array($eventType, ['public', 'private', 'sameforall', 'protected'], true)) {
            return $this->errorResponse('Validation failed', ['event_type' => ['Invalid event type']], 422);
        }
        if (trim((string) $title) === '') {
            return $this->errorResponse('Validation failed', ['event_title' => ['The event title field is required.']], 422);
        }
        $start = $this->parseDateTime((string) $from);
        $end = $this->parseDateTime((string) $to);
        if (! $start || ! $end) {
            return $this->errorResponse('Validation failed', ['start_date' => ['Invalid date format']], 422);
        }

        DB::table('events')->where('id', $id)->update([
            'event_title' => trim((string) $title),
            'event_description' => (string) $description,
            'start_date' => $start,
            'end_date' => $end,
            'event_type' => $eventType,
            'event_color' => $eventColor ?: $row->event_color,
            'event_for' => $this->resolveEventFor($eventType, $userId, $roleId),
        ]);

        return $this->successResponse(null, 'Event updated successfully');
    }

    /** CI updateevent يرسل eventid في body عبر POST — نحوّله لـ updateEvent(id) */
    public function updateEventByBody(Request $request): JsonResponse
    {
        $id = (int) $request->input('eventid', $request->input('event_id', $request->input('id', 0)));
        if ($id <= 0) {
            return $this->errorResponse('Validation failed', ['eventid' => ['The eventid field is required.']], 422);
        }

        return $this->updateEvent($request, $id);
    }

    public function deleteEvent(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_delete')) {
            return $deny;
        }
        $row = DB::table('events')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Event not found', null, 404);
        }
        DB::table('events')->where('id', $id)->delete();

        return $this->successResponse(null, 'Event deleted successfully');
    }

    public function todos(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);
        $v = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            // نمط CI: tasklist بحد 10 وإزاحة (uri segment 4) — ندعم offset/limit كبديل لـ page
            'offset' => 'nullable|integer|min:0',
        ]);
        // parity مع CI: $config['per_page'] = 10 في Calendar::events()
        $perPage = (int) ($v['per_page'] ?? $v['limit'] ?? 10);
        $page = (int) ($v['page'] ?? 1);
        if (! empty($v['offset']) && empty($request->input('page'))) {
            $page = (int) floor(((int) $v['offset']) / $perPage) + 1;
        }

        // parity مع CI getTask: event_type=task + event_for=user + role_id=role مرتبة is_active,start_date
        $base = DB::table('events')
            ->where('event_type', 'task')
            ->where('event_for', $userId)
            ->where('role_id', $roleId);
        // ملخص مثل عدّادات CI (countrows + حالة الإنجاز لكل صف)
        $total = (clone $base)->count();
        $completed = (clone $base)->where('is_active', 'yes')->count();

        $rows = (clone $base)
            ->orderBy('is_active')->orderBy('start_date')
            ->paginate($perPage, ['*'], 'page', $page);

        // حقول العرض كما في CI eventcalendar.php: checkbox حسب is_active،
        // والعنوان بخط مشطوب عند الإنجاز، والتاريخ بصيغة المدرسة (d/m/Y هنا)
        $rows->getCollection()->transform(function ($r) {
            $arr = (array) $r;
            $arr['is_completed'] = ($r->is_active ?? 'no') === 'yes';
            $ts = strtotime((string) ($r->start_date ?? ''));
            $arr['display_date'] = $ts ? date('d/m/Y', $ts) : '';
            $arr['display_title'] = (string) ($r->event_title ?? '');

            return $arr;
        });

        return $this->successResponse([
            'todos' => $rows,
            'summary' => [
                'total' => $total,
                'completed' => $completed,
                'incomplete' => $total - $completed,
            ],
        ]);
    }

    public function addTodo(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        [$userId, $roleId] = $this->visibilityKeys($ctx);

        // أسماء CI: task_title/task_date/eventid
        $title = $request->input('event_title', $request->input('task_title'));
        $date = $request->input('start_date', $request->input('task_date'));
        $eventId = (int) $request->input('eventid', $request->input('id', 0));

        if ($eventId > 0) {
            // تحديث مهمة موجودة — CI addtodo عند وجود eventid (يتطلب can_edit)
            if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_edit')) {
                return $deny;
            }
            $row = DB::table('events')->where('id', $eventId)->where('event_type', 'task')->first();
            if (! $row) {
                return $this->errorResponse('Task not found', null, 404);
            }
            if (trim((string) $title) === '' || trim((string) $date) === '') {
                return $this->errorResponse('Validation failed', ['task_title' => ['The task title and date fields are required.']], 422);
            }
            $start = $this->parseDateTime((string) $date);
            if (! $start) {
                return $this->errorResponse('Validation failed', ['task_date' => ['Invalid date format']], 422);
            }
            DB::table('events')->where('id', $eventId)->update([
                'event_title' => trim((string) $title),
                'start_date' => $start,
                'end_date' => $start,
            ]);

            return $this->successResponse(['id' => $eventId], 'Task updated successfully');
        }

        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_add')) {
            return $deny;
        }
        if (trim((string) $title) === '' || trim((string) $date) === '') {
            return $this->errorResponse('Validation failed', ['task_title' => ['The task title and date fields are required.']], 422);
        }
        $start = $this->parseDateTime((string) $date);
        if (! $start) {
            return $this->errorResponse('Validation failed', ['task_date' => ['Invalid date format']], 422);
        }

        $id = DB::table('events')->insertGetId([
            'event_title' => trim((string) $title),
            'event_description' => '',
            'start_date' => $start,
            'end_date' => $start,
            'event_type' => 'task',
            'event_color' => '#000000',
            'event_for' => $userId,
            'role_id' => $roleId,
            'status' => 'no',
            'is_active' => 'no',
        ]);

        return $this->successResponse(['id' => $id], 'Task added successfully');
    }

    public function markTodoComplete(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        // CI markcomplete بلا فحص — نطلب can_view كحد أدنى + ملكية المهمة (hardening موثق)
        if ($deny = $this->requirePrivilege((int) $ctx->staff->id, 'can_view')) {
            return $deny;
        }
        [$userId, $roleId] = $this->visibilityKeys($ctx);
        $row = DB::table('events')->where('id', $id)
            ->where('event_type', 'task')->where('event_for', $userId)->where('role_id', $roleId)->first();
        if (! $row) {
            return $this->errorResponse('Task not found', null, 404);
        }
        // CI يقبل yes/no للتبديل — ندعم active=yes|no|1|0|true|false
        $raw = strtolower(trim((string) $request->input('active', $request->input('is_active', $request->input('status', 'yes')))));
        $status = in_array($raw, ['yes', '1', 'true', 'completed', 'complete'], true) ? 'yes' : 'no';
        DB::table('events')->where('id', $id)->update(['is_active' => $status]);

        return $this->successResponse(null, $status === 'yes' ? 'Task marked complete' : 'Task marked incomplete');
    }
}
