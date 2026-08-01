<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatUser;
use App\Models\ChatConnection;
use App\Models\ChatMessage;
use App\Services\StudentSessionService;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class ChatController extends \App\Http\Controllers\Api\Controller
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
