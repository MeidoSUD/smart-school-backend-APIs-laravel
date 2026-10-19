<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * شات المعلم — parity مع CI admin/Chat (الجيل الجديد chat_users/connections/messages):
 * - myuser: محادثاتي مع آخر رسالة + إشعارات غير المقروءة
 * - searchuser: بحث موظفين/طلاب خارج محادثاتي (مثل Chatuser_model::searchForUser)
 * - adduser: بدء محادثة (مثل Chatuser_model add)
 * - messages: سجل محادثة + تعليم كمقروء (مثل get_chat + seen)
 * - send: إرسال رسالة
 * - seen: تعليم كمقروء | deleteMessage: حذف رسالتي
 */
class TeacherChatController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherChatController');
    }

    private function myChatUserId(int $staffId): int
    {
        $row = DB::table('chat_users')->where('staff_id', $staffId)->first();
        if ($row) {
            return (int) $row->id;
        }

        return (int) DB::table('chat_users')->insertGetId([
            'user_type' => 'staff',
            'staff_id' => $staffId,
            'student_id' => null,
            'is_active' => 1,
            'created_at' => now(),
        ]);
    }

    public function myConversations(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $chatUserId = $this->myChatUserId((int) $ctx->staff->id);

        $connections = DB::table('chat_connections')
            ->where('chat_user_one', $chatUserId)->orWhere('chat_user_two', $chatUserId)
            ->orderByDesc('id')->get();

        $result = $connections->map(function ($c) use ($chatUserId) {
            $otherId = (int) $c->chat_user_one === $chatUserId ? (int) $c->chat_user_two : (int) $c->chat_user_one;
            $other = DB::table('chat_users')->where('id', $otherId)->first();
            $detail = null;
            if ($other) {
                if ($other->staff_id) {
                    $s = DB::table('staff')->where('id', $other->staff_id)->first(['id', 'name', 'surname', 'image']);
                    $detail = ['type' => 'staff', 'id' => $s->id ?? null, 'name' => trim(($s->name ?? '').' '.($s->surname ?? '')), 'image' => $s->image ?? null];
                } elseif ($other->student_id) {
                    $s = DB::table('students')->where('id', $other->student_id)->first(['id', 'firstname', 'lastname', 'image']);
                    $detail = ['type' => 'student', 'id' => $s->id ?? null, 'name' => trim(($s->firstname ?? '').' '.($s->lastname ?? '')), 'image' => $s->image ?? null];
                }
            }
            $last = DB::table('chat_messages')->where('chat_connection_id', $c->id)->orderByDesc('id')->first();
            $unread = DB::table('chat_messages')->where('chat_connection_id', $c->id)
                ->where('chat_user_id', '!=', $chatUserId)->where('is_read', 0)->count();

            return [
                'connection_id' => $c->id,
                'other' => $detail,
                'last_message' => $last,
                'unread' => $unread,
            ];
        });

        return $this->successResponse(['conversations' => $result]);
    }

    public function searchUser(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate(['keyword' => 'nullable|string|max:100']);

        $kw = trim($v['keyword'] ?? '');
        $chatUserId = $this->myChatUserId((int) $ctx->staff->id);

        $usedIds = DB::table('chat_connections')
            ->where('chat_user_one', $chatUserId)->orWhere('chat_user_two', $chatUserId)
            ->get()->map(fn ($c) => (int) $c->chat_user_one === $chatUserId ? (int) $c->chat_user_two : (int) $c->chat_user_one)->all();
        $usedChatUsers = DB::table('chat_users')->whereIn('id', $usedIds ?: [0])->get();
        $usedStaff = $usedChatUsers->whereNotNull('staff_id')->pluck('staff_id')->map(fn ($x) => (int) $x)->all();
        $usedStudents = $usedChatUsers->whereNotNull('student_id')->pluck('student_id')->map(fn ($x) => (int) $x)->all();

        $staffQ = DB::table('staff')->where('is_active', 1)->where('id', '!=', $ctx->staff->id)
            ->when($usedStaff !== [], fn ($q) => $q->whereNotIn('id', $usedStaff))
            ->when($kw !== '', fn ($q) => $q->where('name', 'like', "%{$kw}%"))
            ->select('id', 'name', 'surname', 'image')->limit(20)->get();

        $studentQ = DB::table('students')->where('is_active', 'yes')
            ->when($usedStudents !== [], fn ($q) => $q->whereNotIn('id', $usedStudents))
            ->when($kw !== '', fn ($q) => $q->where(fn ($qq) => $qq->where('firstname', 'like', "%{$kw}%")->orWhere('lastname', 'like', "%{$kw}%")))
            ->select('id', 'firstname', 'lastname', 'image')->limit(20)->get();

        return $this->successResponse(['staff' => $staffQ, 'students' => $studentQ]);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'staff_id' => 'nullable|integer|exists:staff,id',
            'student_id' => 'nullable|integer|exists:students,id',
            'message' => 'nullable|string|max:2000',
        ]);

        if (empty($v['staff_id']) && empty($v['student_id'])) {
            return $this->errorResponse('staff_id or student_id is required', null, 422);
        }

        $myId = $this->myChatUserId((int) $ctx->staff->id);

        $other = DB::table('chat_users')
            ->when(! empty($v['staff_id']), fn ($q) => $q->where('staff_id', $v['staff_id']))
            ->when(! empty($v['student_id']), fn ($q) => $q->where('student_id', $v['student_id']))
            ->first();
        $otherId = $other ? (int) $other->id : (int) DB::table('chat_users')->insertGetId([
            'user_type' => ! empty($v['staff_id']) ? 'staff' : 'student',
            'staff_id' => $v['staff_id'] ?? null,
            'student_id' => $v['student_id'] ?? null,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        $existing = DB::table('chat_connections')
            ->where(fn ($q) => $q->where('chat_user_one', $myId)->where('chat_user_two', $otherId))
            ->orWhere(fn ($q) => $q->where('chat_user_one', $otherId)->where('chat_user_two', $myId))
            ->first();

        $connectionId = $existing ? (int) $existing->id : (int) DB::table('chat_connections')->insertGetId([
            'chat_user_one' => $myId,
            'chat_user_two' => $otherId,
            'time' => time(),
            'created_at' => now(),
        ]);

        if (! empty($v['message'])) {
            DB::table('chat_messages')->insert([
                'chat_connection_id' => $connectionId,
                'chat_user_id' => $myId,
                'message' => $v['message'],
                'is_read' => 0,
                'time' => time(),
                'ip' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        return $this->successResponse(['connection_id' => $connectionId], 'Conversation ready');
    }

    public function messages(Request $request, int $connectionId): JsonResponse
    {
        $ctx = $this->context($request);
        $myId = $this->myChatUserId((int) $ctx->staff->id);

        $conn = DB::table('chat_connections')->where('id', $connectionId)->first();
        if (! $conn || ((int) $conn->chat_user_one !== $myId && (int) $conn->chat_user_two !== $myId)) {
            return $this->errorResponse('Conversation not found', null, 404);
        }

        DB::table('chat_messages')->where('chat_connection_id', $connectionId)
            ->where('chat_user_id', '!=', $myId)->update(['is_read' => 1]);

        $list = DB::table('chat_messages')->where('chat_connection_id', $connectionId)
            ->orderBy('id')->paginate(50);

        return $this->successResponse(['messages' => $list]);
    }

    public function send(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'connection_id' => 'required|integer|exists:chat_connections,id',
            'message' => 'required|string|max:2000',
        ]);

        $myId = $this->myChatUserId((int) $ctx->staff->id);
        $conn = DB::table('chat_connections')->where('id', $v['connection_id'])->first();
        if (! $conn || ((int) $conn->chat_user_one !== $myId && (int) $conn->chat_user_two !== $myId)) {
            return $this->errorResponse('Conversation not found', null, 404);
        }

        $id = DB::table('chat_messages')->insertGetId([
            'chat_connection_id' => $v['connection_id'],
            'chat_user_id' => $myId,
            'message' => $v['message'],
            'is_read' => 0,
            'time' => time(),
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $id], 'Message sent');
    }
}
