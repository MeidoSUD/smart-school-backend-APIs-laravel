<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherScope;

/**
 * مركز التحميل (المحتوى المشترك) — parity مع CI Download Center:
 * - content_share_list: المحتوى المتاح (حسب group/student/class_section)
 * - upload_content: تسجيل محتوى جديد من المعلم (رفع الملف نفسه خارج النطاق — يقبل `document` كمسار)
 */
class TeacherContentController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherContentController');
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $csIds = collect($classes)->pluck('class_section_id')->all();

        $query = DB::table('share_contents')
            ->leftJoin('staff', 'share_contents.created_by', '=', 'staff.id')
            ->select('share_contents.*', 'staff.name as created_by_name', 'staff.surname as created_by_surname')
            ->orderByDesc('share_contents.share_date');

        if (! empty($csIds)) {
            $query->where(function ($q) use ($ctx, $csIds) {
                $q->where('share_contents.created_by', $ctx->staff->id)
                    ->orWhereIn('share_contents.id', function ($qq) use ($csIds) {
                        $qq->select('share_content_id')->from('share_content_for')
                            ->whereIn('class_section_id', $csIds);
                    });
            });
        } else {
            $query->where('share_contents.created_by', $ctx->staff->id);
        }

        return $this->successResponse(['contents' => $query->paginate(25)]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'title' => 'required|string|max:255', 'description' => 'nullable|string',
            'send_to' => 'nullable|string|max:50', 'share_date' => 'nullable|date',
            'valid_upto' => 'nullable|date|after_or_equal:share_date',
            'document' => 'nullable|string|max:255',
            'class_section_ids' => 'nullable|array', 'class_section_ids.*' => 'integer',
        ]);

        if (! empty($v['class_section_ids'])) {
            foreach ($v['class_section_ids'] as $csId) {
                if (! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, (int) $csId)) {
                    return $this->errorResponse('One of the class sections is not in your scope', null, 403);
                }
            }
        }

        $id = 0;
        DB::transaction(function () use ($v, $ctx, &$id) {
            $id = DB::table('share_contents')->insertGetId([
                'send_to' => $v['send_to'] ?? 'all', 'title' => $v['title'],
                'share_date' => $v['share_date'] ?? date('Y-m-d'),
                'valid_upto' => $v['valid_upto'] ?? null,
                'description' => $v['description'] ?? null,
                'created_by' => $ctx->staff->id, 'created_at' => now(),
            ]);
            foreach ($v['class_section_ids'] ?? [] as $csId) {
                DB::table('share_content_for')->insert([
                    'share_content_id' => $id, 'class_section_id' => $csId,
                ]);
            }
        });

        return $this->successResponse(['id' => $id], 'Content shared successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $row = DB::table('share_contents')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Content not found', null, 404);
        }
        $for = DB::table('share_content_for')->where('share_content_id', $id)->get();

        return $this->successResponse(['content' => $row, 'shared_for' => $for]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $row = DB::table('share_contents')->where('id', $id)->where('created_by', $ctx->staff->id)->first();
        if (! $row) {
            return $this->errorResponse('Content not found', null, 404);
        }

        $v = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'valid_upto' => 'sometimes|nullable|date',
            'document' => 'sometimes|nullable|string|max:255',
        ]);

        // CI parity: share_contents has no `document` column — attachments live in
        // share_upload_contents/upload_contents. Never write `document` to share_contents.
        unset($v['document']);

        if ($v === []) {
            return $this->successResponse(null, 'Content updated successfully');
        }

        DB::table('share_contents')->where('id', $id)->update($v);

        return $this->successResponse(null, 'Content updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $row = DB::table('share_contents')->where('id', $id)->where('created_by', $ctx->staff->id)->first();
        if (! $row) {
            return $this->errorResponse('Content not found', null, 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('share_content_for')->where('share_content_id', $id)->delete();
            DB::table('share_contents')->where('id', $id)->delete();
        });

        return $this->successResponse(null, 'Content deleted successfully');
    }
}
