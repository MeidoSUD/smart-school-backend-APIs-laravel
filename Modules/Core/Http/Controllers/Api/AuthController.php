<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Modules\Core\Entities\User;
use Modules\Academic\Entities\Student;
use Modules\Staff\Entities\Staff;
use Modules\Core\Entities\Classe;
use Modules\Core\Entities\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Services\ApiLogger;
use Modules\Core\Services\StudentSessionResolver;
use Modules\Finance\Services\StudentFeeService;

class AuthController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private StudentSessionResolver $sessionResolver,
        private StudentFeeService $feeService,
    ) {
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
            ApiLogger::logAuth('login_failed', $credentials['username'], false, $user->id);

            return $this->errorResponse('Invalid username or password', null, 401);
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

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

                    $studentSession = $this->sessionResolver->resolveSessionForStudent((int) $student->id);
                    if ($studentSession) {
                        $data = array_merge($data, $this->sessionPayload($studentSession));
                        $data['fees'] = $this->feeService->feeSummary($studentSession->id);
                    }
                }
                break;

            case 'parent':
                $children = $this->sessionResolver->parentChildrenWithSessions($user);
                $data['childs'] = $this->enrichChildrenPayload($children, $user->childs);

                if (!empty($children)) {
                    $defaultChild = $children[0];
                    $studentSession = $this->sessionResolver->resolveSessionForStudent((int) $defaultChild['student_id']);
                    if ($studentSession) {
                        $data['student_id'] = $defaultChild['student_id'];
                        $data = array_merge($data, $this->sessionPayload($studentSession));
                        $data['fees'] = $this->feeService->feeSummary($studentSession->id);
                    }
                }
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

    private function sessionPayload($studentSession): array
    {
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        return [
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'student_session_id' => $studentSession->id,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     */
    private function enrichChildrenPayload(array $children, ?string $legacyChilds): array
    {
        $legacy = json_decode($legacyChilds ?? '[]', true) ?? [];

        if (empty($children)) {
            return is_array($legacy) ? $legacy : [];
        }

        return array_map(function ($child) {
            $class = Classe::find($child['class_id'] ?? null);
            $section = Section::find($child['section_id'] ?? null);

            return array_merge($child, [
                'class' => $class ? $class->class : null,
                'section' => $section ? $section->section : null,
            ]);
        }, $children);
    }
}
