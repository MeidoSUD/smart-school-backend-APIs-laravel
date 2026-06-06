<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Modules\Core\Entities\User;
use Modules\Academic\Entities\Student;
use Modules\Staff\Entities\Staff;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Setting;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Modules\Finance\Entities\StudentFeeMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Services\ApiLogger;

class AuthController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('AuthController');
    }

public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        ApiLogger::logAuth('login_attempt', $credentials['username'], false);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            ApiLogger::logAuth('login_failed', $credentials['username'], false);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        if (!$user->isActive()) {
            ApiLogger::logAuth('login_disabled', $credentials['username'], false, $user->id);
            return $this->errorResponse('Your account is disabled, please contact administrator.', null, 403);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            if ($user->password !== $credentials['password']) {
                ApiLogger::logAuth('login_failed', $credentials['username'], false, $user->id);
                return $this->errorResponse('Invalid username or password', null, 401);
            }
            $user->password = Hash::make($credentials['password']);
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        $user->save();

        ApiLogger::logAuth('login_success', $credentials['username'], true, $user->id);

        $userData = $this->buildUserResponse($user);

        return $this->successResponse([
            'token' => $token,
            'user' => $userData,
        ], 'Login successful');
    }

public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $userId = $user->id;
            $username = $user->username;

            if ($request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            } else {
                $user->tokens()->delete();
            }

            ApiLogger::logAuth('logout', $username, true, $userId);

            return $this->successResponse(null, 'Logged out successfully');
        }

        return $this->errorResponse('Not logged in', null, 401);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        if ($user->role === 'guest') {
            return $this->errorResponse('Guest users cannot change password here');
        }

        $validated = $request->validated();

        if (!Hash::check($validated['current_pass'], $user->password)) {
            return $this->errorResponse('Invalid current password');
        }

        $user->password = Hash::make($validated['new_pass']);
        $user->save();

        $user->tokens()->delete();

        return $this->successResponse(null, 'Password changed successfully');
    }

    private function buildUserResponse(User $user): array
    {
        $data = $user->toArray();
        unset($data['password']);

        switch ($user->role) {
            case 'student':
                $student = Student::find($user->user_id);
                if ($student) {
                    $data = array_merge($data, $student->toArray());

                    $studentSession = $this->getStudentDefaultSession($student->id);
                    if ($studentSession) {
                        $data['class_id'] = $studentSession->class_id;
                        $data['section_id'] = $studentSession->section_id;
                        $data['student_session_id'] = $studentSession->id;

                        $class = Classe::find($studentSession->class_id);
                        $section = Section::find($studentSession->section_id);
                        $data['class'] = $class ? $class->class : null;
                        $data['section'] = $section ? $section->section : null;
                        
                        $data['fees'] = $this->getStudentFeeSummary($studentSession->id);
                    }
                }
                break;

            case 'parent':
                $child = Student::where('parent_id', $user->id)->first();
                if ($child) {
                    $studentSession = $this->getStudentDefaultSession($child->id);
                    if ($studentSession) {
                        $data['student_id'] = $child->id;
                        $data['class_id'] = $studentSession->class_id;
                        $data['section_id'] = $studentSession->section_id;
                        $data['student_session_id'] = $studentSession->id;
                        
                        $class = Classe::find($studentSession->class_id);
                        $section = Section::find($studentSession->section_id);
                        $data['class'] = $class ? $class->class : null;
                        $data['section'] = $section ? $section->section : null;
                        
                        $data['fees'] = $this->getStudentFeeSummary($studentSession->id);
                    }
                }
                $data['childs'] = json_decode($user->childs, true) ?? [];
                break;

            case 'teacher':
            case 'staff':
            case 'accountant':
            case 'librarian':
                $staff = Staff::where('user_id', $user->id)->first();
                if ($staff) {
                    $data = array_merge($data, $staff->toArray());
                }
                break;
        }

        unset($data['password']);
        return $data;
    }

    private function getStudentDefaultSession(int $studentId): ?StudentSession
    {
        $setting = Setting::where('is_active', 1)->first();

        $query = StudentSession::where('student_id', $studentId);

        if ($setting) {
            $query->where('session_id', $setting->id);
        }

        $studentSession = $query->first();

        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first();
        }

        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return $studentSession;
    }

    private function getStudentFeeSummary(int $studentSessionId): array
    {
        $fees = StudentFeeMaster::where('student_session_id', $studentSessionId)
            ->get();
            
        return [
            'total_fees' => $fees->sum('amount'),
            'fees_list' => $fees->toArray()
        ];
    }
}
