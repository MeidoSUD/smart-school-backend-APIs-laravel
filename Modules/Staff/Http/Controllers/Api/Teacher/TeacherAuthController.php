<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Entities\User;
use Modules\Core\Services\ApiLogger;
use Modules\Staff\Entities\Staff;
use Modules\Staff\Services\TeacherScope;

/**
 * دخول المعلم — parity مع CodeIgniter:
 * - CI: Site::login → Staff_model::checkLogin (بحث بالإيميل في staff ثم
 *   password_verify عبر Enc_lib::passHashDyc، ثم الدور الأول من staff_roles).
 * - API: يقبل username (صف users، نص صريح — مثل AuthController الحالي)
 *   أو email (صف staff، bcrypt — مثل CI). أيهما وُجد يُستخدم.
 */
class TeacherAuthController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherAuthController');
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $identifier = trim($validated['identifier']);
        $password = $validated['password'];

        ApiLogger::logAuth('teacher_login_attempt', $identifier, false);

        [$user, $staff] = $this->findTeacher($identifier, $password);

        if (! $user || ! $staff) {
            ApiLogger::logAuth('teacher_login_failed', $identifier, false);

            return $this->errorResponse('Invalid username or password', null, 401);
        }

        if (($staff->is_active != 1 && $staff->is_active !== '1') || ! $user->isActive()) {
            ApiLogger::logAuth('teacher_login_disabled', $identifier, false, $user->id);

            return $this->errorResponse('Your account is disabled, please contact administrator.', null, 403);
        }

        $token = $user->createToken('api-token', ['teacher'])->plainTextToken;

        ApiLogger::logAuth('teacher_login_success', $identifier, true, $user->id);

        $roles = TeacherScope::staffRoles((int) $staff->id);
        $sessionId = TeacherScope::currentSessionId();

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'staff' => [
                'id' => $staff->id,
                'employee_id' => $staff->employee_id,
                'name' => $staff->name,
                'surname' => $staff->surname,
                'email' => $staff->email,
                'contact_no' => $staff->contact_no,
                'image' => $staff->image,
                'gender' => $staff->gender,
            ],
            'roles' => $roles,
            'session_id' => $sessionId,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Not logged in', null, 401);
        }

        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        } else {
            $user->tokens()->delete();
        }

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        return $this->successResponse([
            'user' => ['id' => $ctx->user->id, 'username' => $ctx->user->username, 'role' => $ctx->user->role],
            'staff' => $ctx->staff,
            'roles' => $ctx->roles,
            'session_id' => $ctx->sessionId,
        ]);
    }

    /**
     * @return array{0: ?User, 1: ?Staff}
     */
    private function findTeacher(string $identifier, string $password): array
    {
        // 1) صف users بالـ username. يقبل النص الصريح (مثل باقي الـ API)
        // أو bcrypt (بعض صفوف المعلمين مخزنة مشفّرة في القاعدة الحالية).
        $user = User::where('username', $identifier)->where('role', 'teacher')->first();
        if ($user && $this->passwordMatches($password, $user->password)) {
            $staff = TeacherScope::resolveStaff($user);
            if ($staff) {
                return [$user, $staff];
            }
        }

        // 2) صف staff بالإيميل (bcrypt — parity مع CI Site::login).
        $staff = Staff::where('email', $identifier)->first();
        if ($staff && $staff->password && Hash::check($password, $staff->password)) {
            $linked = User::where('role', 'teacher')
                ->where(function ($q) use ($staff) {
                    $q->where('user_id', $staff->id)->orWhere('id', $staff->user_id);
                })
                ->first();
            if ($linked) {
                return [$linked, $staff];
            }
        }

        return [null, null];
    }

    private function passwordMatches(string $plain, ?string $stored): bool
    {
        if ($stored === null || $stored === '') {
            return false;
        }
        if (hash_equals($stored, $plain)) {
            return true;
        }
        try {
            return Hash::check($plain, $stored);
        } catch (\Throwable) {
            return false;
        }
    }
}
