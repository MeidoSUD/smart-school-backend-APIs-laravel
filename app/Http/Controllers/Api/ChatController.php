<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatUser;
use App\Models\ChatConnection;
use App\Models\ChatMessage;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Chat.php
 */
class ChatController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->successResponse(['title' => 'Chat']);
    }

    public function myuser(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);
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

    public function getChatRecord(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        $chatUser = ChatUser::where('student_id', $studentId)->where('user_type', 'student')->first();
        
        $chatConnectionId = $request->chat_connection_id;
        $chatToUser = 0;
        
        $userLastChat = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->orderBy('id', 'desc')
            ->first();
        
        $chatConnection = ChatConnection::find($chatConnectionId);
        
        if ($chatConnection) {
            $chatToUser = $chatConnection->chat_user_one;
            if ($chatConnection->chat_user_one == ($chatUser ? $chatUser->id : 0)) {
                $chatToUser = $chatConnection->chat_user_two;
            }
        }
        
        $chatList = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->where('chat_user_id', '!=', $chatUser ? $chatUser->id : 0)
            ->update(['is_read' => 1]);
        
        $chatList = ChatMessage::where('chat_connection_id', $chatConnectionId)
            ->orderBy('id', 'asc')
            ->get();
        
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
        
        $insertRecord = ChatMessage::create([
            'chat_user_id' => $request->chat_to_user,
            'message' => trim($request->message),
            'chat_connection_id' => $request->chat_connection_id,
            'created_at' => now(),
        ]);
        
        return $this->successResponse(['last_insert_id' => $insertRecord->id], 'Message sent');
    }

    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
        }
        return null;
    }

    private function getMyUserList($studentId, $chatUserId)
    {
        $connections = ChatConnection::where('chat_user_one', $chatUserId)
            ->orWhere('chat_user_two', $chatUserId)
            ->get();
        
        $userList = [];
        foreach ($connections as $conn) {
            $otherUserId = $conn->chat_user_one == $chatUserId ? $conn->chat_user_two : $conn->chat_user_one;
            $otherUser = ChatUser::find($otherUserId);
            if ($otherUser) {
                $userList[] = $otherUser;
            }
        }
        
        return $userList;
    }
}