<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\ChatUser;
use Modules\Operations\Entities\ChatConnection;
use Modules\Operations\Entities\ChatMessage;
use Modules\Academic\Entities\Student;
use Modules\Staff\Entities\Staff;
use Modules\Core\Services\StudentSessionService;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class ChatController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('ChatController');
    }

    public function index(): JsonResponse
    {
        return $this->successResponse(['title' => 'Chat']);
        }



    public function myuser(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();
        
        $data = [
            'chat_user' => $chatUser ? [$chatUser] : [],
            'userList' => [],
        ];
        
        if ($chatUser) {
            $data['userList'] = $this->getMyUserList($studentId, $chatUser->id);
            }

        
        return $this->successResponse($data);
        }



    #[BodyParameter('chat_connection_id', description: 'Chat connection ID', type: 'integer', required: true, example: 1)]
    public function getChatRecord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_connection_id' => 'required|integer|exists:chat_connections,id',
        ]);

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();
        
        if (!$chatUser) {
            return $this->errorResponse('Chat user not found', null, 404);
        }

        $chatConnectionId = $request->chat_connection_id;
        
        $chatConnection = ChatConnection::where('id', $chatConnectionId)
            ->where(function ($query) use ($chatUser) {
                $query->where('chat_user_one', $chatUser->id)
                    ->orWhere('chat_user_two', $chatUser->id);
            })
            ->first();

        if (!$chatConnection) {
            return $this->errorResponse('Chat connection not found or access denied', null, 404);
        }

        $chatToUser = $chatConnection->chat_user_one == $chatUser->id
            ? $chatConnection->chat_user_two
            : $chatConnection->chat_user_one;

        ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->where('chat_user_id', '!=', $chatUser->id)
            ->update(['is_read' => 1]);
        
        $chatList = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->orderBy('id', 'asc')
            ->get();
        
        $userLastChat = $chatList->last();

        return $this->successResponse([
            'chatList' => $chatList,
            'chat_to_user' => $chatToUser,
            'chat_connection_id' => $chatConnectionId,
            'user_last_chat' => $userLastChat,
        ]);
    }



    public function newMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_connection_id' => 'required',
            'chat_to_user' => 'required',
            'message' => 'required|string',
        ]);
        
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::firstOrCreate(
            ['student_id' => $studentId, 'user_type' => 'student'],
            ['student_id' => $studentId, 'user_type' => 'student']
        );
        
        $insertRecord = DB::transaction(function () use ($request, $chatUser) {
            $chatConnectionId = $request->chat_connection_id;
            
            if ($chatConnectionId == 0 || !ChatConnection::find($chatConnectionId)) {
                $chatConnection = ChatConnection::create([
                    'chat_user_one' => $chatUser->id,
                    'chat_user_two' => $request->chat_to_user,
                    'ip' => $request->ip(),
                    'time' => time(),
                ]);
                $chatConnectionId = $chatConnection->id;
            }
            
            return ChatMessage::create([
                'chat_user_id' => $request->chat_to_user,
                'message' => trim($request->message),
                'chat_connection_id' => $chatConnectionId,
                'ip' => $request->ip(),
                'time' => time(),
                'created_at' => now(),
            ]);
        });
        
        return $this->successResponse(['last_insert_id' => $insertRecord->id, 'chat_connection_id' => $insertRecord->chat_connection_id], 'Message sent');
    }

    #[BodyParameter('chat_connection_id', description: 'Chat connection ID', type: 'integer', required: true, example: 1)]
    #[BodyParameter('last_chat_id', description: 'ID of the last received chat message to fetch newer ones', type: 'integer', required: true, example: 0)]
    public function chatUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_connection_id' => 'required|integer',
            'last_chat_id' => 'required|integer',
        ]);

        $chatConnectionId = $validated['chat_connection_id'];
        $lastChatId = $validated['last_chat_id'];

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();

        $updatedChat = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->where('id', '>', $lastChatId)
            ->orderBy('id', 'asc')
            ->get();

        $userLastChat = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->orderBy('id', 'desc')
            ->first();

        if ($chatUser) {
            ChatMessage::where('chat_connection_id', $chatConnectionId)
                ->where('chat_user_id', '!=', $chatUser->id)
                ->where('id', '<=', $lastChatId)
                ->update(['is_read' => 1]);
        }

        return $this->successResponse([
            'updated_chat' => $updatedChat,
            'last_chat_id' => $lastChatId,
            'user_last_chat' => $userLastChat,
        ]);
    }

    public function mychatnotification(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();

        $notifications = [];

        if ($chatUser) {
            $notifications = ChatMessage::where('chat_user_id', '!=', $chatUser->id)
                ->where('is_read', 0)
                ->get()
                ->map(fn ($message) => [
                    'chat_connection_id' => $message->chat_connection_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                ])
                ->groupBy('chat_connection_id')
                ->map(fn ($group) => $group->values())
                ->values()
                ->toArray();
        }

        return $this->successResponse([
            'notifications' => $notifications,
            'count' => count($notifications),
        ]);
    }

    #[BodyParameter('keyword', description: 'Search keyword for users (name or employee/admission number)', type: 'string', required: true, example: 'John')]
    public function searchuser(Request $request): JsonResponse
    {
        $keyword = $request->input('keyword');

        if (! $keyword) {
            return $this->errorResponse('keyword is required');
        }

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();
        $connectedUserIds = $this->getConnectedUserIds($chatUser ? $chatUser->id : null);

        $results = collect();

        $staff = Staff::where('is_active', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('surname', 'like', "%{$keyword}%")
                    ->orWhere('employee_id', 'like', "%{$keyword}%");
            })
            ->get();

        foreach ($staff as $member) {
            $results->push([
                'id' => $member->id,
                'user_type' => 'Staff',
                'name' => trim(($member->name ?? '') . ' ' . ($member->surname ?? '')),
                'image' => $member->image,
                'gender' => $member->gender,
                'is_connected' => in_array($member->id, $connectedUserIds['staff'], true),
            ]);
        }

        $students = Student::where('is_active', 1)
            ->where('id', '!=', $studentId)
            ->where(function ($query) use ($keyword) {
                $query->where('firstname', 'like', "%{$keyword}%")
                    ->orWhere('middlename', 'like', "%{$keyword}%")
                    ->orWhere('lastname', 'like', "%{$keyword}%")
                    ->orWhere('admission_no', 'like', "%{$keyword}%");
            })
            ->get();

        foreach ($students as $student) {
            $results->push([
                'id' => $student->id,
                'user_type' => 'Student',
                'name' => trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? '')),
                'image' => $student->image,
                'gender' => $student->gender,
                'is_connected' => in_array($student->id, $connectedUserIds['student'], true),
            ]);
        }

        return $this->successResponse(['userList' => $results->values()]);
    }

    #[BodyParameter('users', description: 'List of user IDs the client already knows (JSON array)', type: 'array', required: false)]
    public function mynewuser(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();
        $connectedUserIds = $this->getConnectedUserIds($chatUser ? $chatUser->id : null);

        $existingUserIds = collect($request->input('users', []))
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->all();

        $newUserList = collect();

        Staff::where('is_active', 1)
            ->whereNotIn('id', $connectedUserIds['staff'])
            ->whereNotIn('id', $existingUserIds)
            ->limit(20)
            ->get()
            ->each(function ($member) use ($newUserList) {
                $newUserList->push([
                    'id' => $member->id,
                    'user_type' => 'Staff',
                    'name' => trim(($member->name ?? '') . ' ' . ($member->surname ?? '')),
                    'image' => $member->image,
                    'gender' => $member->gender,
                ]);
            });

        Student::where('is_active', 1)
            ->where('id', '!=', $studentId)
            ->whereNotIn('id', $connectedUserIds['student'])
            ->whereNotIn('id', $existingUserIds)
            ->limit(20)
            ->get()
            ->each(function ($student) use ($newUserList) {
                $newUserList->push([
                    'id' => $student->id,
                    'user_type' => 'Student',
                    'name' => trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? '')),
                    'image' => $student->image,
                    'gender' => $student->gender,
                ]);
            });

        return $this->successResponse(['new_user_list' => $newUserList->values()]);
    }

    #[BodyParameter('user_id', description: 'ID of the user to add (student or staff ID)', type: 'integer', required: true, example: 5)]
    #[BodyParameter('user_type', description: 'User type of the contact: Student or Staff', type: 'string', required: true, example: 'Staff')]
    public function adduser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'user_type' => 'required|string|in:Student,Staff,student,staff',
        ]);

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $chatUser = ChatUser::firstOrCreate(
            ['student_id' => $studentId, 'user_type' => 'student'],
            ['student_id' => $studentId, 'user_type' => 'student', 'is_active' => 1]
        );

        $userType = strtolower($validated['user_type']);
        $targetUserId = $validated['user_id'];

        $targetChatUser = ChatUser::firstOrCreate(
            [$userType . '_id' => $targetUserId, 'user_type' => $userType],
            [$userType . '_id' => $targetUserId, 'user_type' => $userType, 'is_active' => 1]
        );

        $chatConnection = ChatConnection::where(function ($query) use ($chatUser, $targetChatUser) {
            $query->where('chat_user_one', $chatUser->id)->where('chat_user_two', $targetChatUser->id);
        })->orWhere(function ($query) use ($chatUser, $targetChatUser) {
            $query->where('chat_user_one', $targetChatUser->id)->where('chat_user_two', $chatUser->id);
        })->first();

        $result = DB::transaction(function () use ($chatUser, $targetChatUser, $chatConnection, $request) {
            if (! $chatConnection) {
                $chatConnection = ChatConnection::create([
                    'chat_user_one' => $chatUser->id,
                    'chat_user_two' => $targetChatUser->id,
                    'ip' => $request->ip(),
                    'time' => time(),
                ]);
            }

            $firstMessage = ChatMessage::where('chat_connection_id', $chatConnection->id)
                ->where('is_first', 1)
                ->first();

            if (! $firstMessage) {
                $firstMessage = ChatMessage::create([
                    'message' => 'you are now connected on chat',
                    'chat_user_id' => 0,
                    'chat_connection_id' => $chatConnection->id,
                    'is_first' => 1,
                    'ip' => $request->ip(),
                    'time' => time(),
                    'created_at' => now(),
                ]);
            }

            return ['connection' => $chatConnection, 'message' => $firstMessage];
        });

        $newUser = [
            'id' => $targetUserId,
            'user_type' => ucfirst($userType),
        ];

        if ($userType === 'staff') {
            $member = Staff::find($targetUserId);
            $newUser['name'] = $member ? trim(($member->name ?? '') . ' ' . ($member->surname ?? '')) : '';
            $newUser['image'] = $member->image ?? null;
            $newUser['gender'] = $member->gender ?? null;
        } else {
            $student = Student::find($targetUserId);
            $newUser['name'] = $student ? trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? '')) : '';
            $newUser['image'] = $student->image ?? null;
            $newUser['gender'] = $student->gender ?? null;
        }

        return $this->successResponse([
            'new_user' => $newUser,
            'chat_connection_id' => $result['connection']->id,
            'chat_to_user' => $targetChatUser->id,
            'user_last_chat' => $result['message'],
        ], 'User added successfully');
    }

    private function getConnectedUserIds(?int $chatUserId): array
    {
        $connectedIds = ['staff' => [], 'student' => []];

        if (! $chatUserId) {
            return $connectedIds;
        }

        $connections = ChatConnection::where('chat_user_one', $chatUserId)
            ->orWhere('chat_user_two', $chatUserId)
            ->get();

        $otherUserIds = $connections->map(function ($conn) use ($chatUserId) {
            return $conn->chat_user_one == $chatUserId ? $conn->chat_user_two : $conn->chat_user_one;
        });

        $otherUsers = ChatUser::whereIn('id', $otherUserIds)->get();

        foreach ($otherUsers as $otherUser) {
            if ($otherUser->user_type === 'student' && $otherUser->student_id) {
                $connectedIds['student'][] = $otherUser->student_id;
            } elseif ($otherUser->user_type === 'staff' && $otherUser->staff_id) {
                $connectedIds['staff'][] = $otherUser->staff_id;
            }
        }

        return $connectedIds;
    }

    private function getMyUserList($studentId, $chatUserId)
    {
        $connections = ChatConnection::where('chat_user_one', $chatUserId)
            ->orWhere('chat_user_two', $chatUserId)
            ->get();
        
        $otherUserIds = $connections->map(function ($conn) use ($chatUserId) {
            return $conn->chat_user_one == $chatUserId ? $conn->chat_user_two : $conn->chat_user_one;
        });
        
        $chatUsers = ChatUser::whereIn('id', $otherUserIds)->get()->keyBy('id');
        
        $userList = [];
        foreach ($connections as $conn) {
            $otherUserId = $conn->chat_user_one == $chatUserId ? $conn->chat_user_two : $conn->chat_user_one;
            if (isset($chatUsers[$otherUserId])) {
                $userList[] = $chatUsers[$otherUserId];
            }
        }

        return $userList;
    }
}
