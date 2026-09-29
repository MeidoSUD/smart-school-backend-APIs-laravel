<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\ForgotPasswordRequest;
use Modules\Core\Http\Requests\ResetPasswordRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Modules\Core\Entities\User;
use Modules\Core\Entities\Setting;
use Modules\Academic\Entities\Student;
use Modules\Core\Services\ApiLogger;
use Modules\Core\Services\UserResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly UserResponseService $userResponseService
    ) {
        $this->setControllerName('AuthController');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        // CI parity: Site::userlogin() trims username+password (trim|required|xss_clean).
        $validated = $request->validated();
        $username = trim((string) ($validated['username'] ?? ''));
        $password = trim((string) ($validated['password'] ?? ''));

        ApiLogger::logAuth('login_attempt', $username, false);

        // CI parity: Site::userlogin() gates on sch_settings.student_panel_login
        // (redirects to site/login when disabled). API returns 403 instead.
        $setting = Setting::first();
        $studentLoginMethods = $setting ? (json_decode((string) $setting->student_login, true) ?? []) : [];
        $parentLoginMethods = $setting ? (json_decode((string) $setting->parent_login, true) ?? []) : [];
        if (!is_array($studentLoginMethods)) {
            $studentLoginMethods = [];
        }
        if (!is_array($parentLoginMethods)) {
            $parentLoginMethods = [];
        }

        if (!$setting || empty($setting->student_panel_login)) {
            ApiLogger::logAuth('login_disabled_panel', $username, false);
            return $this->errorResponse('Student panel login is disabled', null, 403);
        }

        // CI parity: User_model::checkLogin() tries student join first
        // (users.user_id = students.id, alternate keys per sch_settings.student_login),
        // then parent join (students.parent_id = users.id, keys per parent_login).
        // Password is plaintext compared in PHP, not in SQL.
        $user = $this->findStudentLogin($username, $password, $studentLoginMethods);
        if (!$user) {
            $user = $this->findParentLogin($username, $password, $parentLoginMethods);
        }

        if (!$user) {
            ApiLogger::logAuth('login_failed', $username, false);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        if (!$user->isActive()) {
            ApiLogger::logAuth('login_disabled', $username, false, $user->id);
            return $this->errorResponse('Your account is disabled, please contact administrator.', null, 403);
        }

        // CI parity second stage:
        // student -> User_model::read_user_information() requires students.is_active='yes'
        // parent  -> requires parent_panel_login + User_model::checkLoginParent() row.
        // Otherwise Site shows 'Account Suspended'.
        if ($user->role === 'student') {
            $activeStudent = Student::where('id', $user->user_id)->where('is_active', 'yes')->exists();
            if (!$activeStudent) {
                ApiLogger::logAuth('login_suspended', $username, false, $user->id);
                return $this->errorResponse('Account Suspended', null, 403);
            }
        }

        if ($user->role === 'parent') {
            if (empty($setting->parent_panel_login)) {
                ApiLogger::logAuth('login_suspended', $username, false, $user->id);
                return $this->errorResponse('Account Suspended', null, 403);
            }
            $hasChild = Student::where('parent_id', $user->id)->exists();
            if (!$hasChild) {
                ApiLogger::logAuth('login_suspended', $username, false, $user->id);
                return $this->errorResponse('Account Suspended', null, 403);
            }
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        ApiLogger::logAuth('login_success', $username, true, $user->id);

        $userData = $this->userResponseService->buildUserResponse($user);

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

        if ($validated['current_pass'] !== $user->password) {
            return $this->errorResponse('Invalid current password');
        }

        DB::transaction(function () use ($user, $validated) {
            $user->password = $validated['new_pass'];
            $user->save();
            $user->tokens()->delete();
        });

        return $this->successResponse(null, 'Password changed successfully');
    }

    /**
     * CI parity: Site::ufpassword() — request a password-reset link by email.
     * student -> students.email (users.user_id = students.id, role student)
     * parent  -> students.guardian_email (students.parent_id = users.id, role parent)
     * Generates users.verification_code and emails the reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = trim((string) ($validated['email'] ?? ''));
        $userType = $validated['user_type'];

        ApiLogger::logAuth('forgot_password_attempt', $email, false);

        $user = $this->findUserByResetEmail($userType, $email);

        if (!$user) {
            ApiLogger::logAuth('forgot_password_failed', $email, false);
            // CI lang line: invalid_email_or_user_type
            return $this->errorResponse('Invalid email or user type', null, 404);
        }

        $verificationCode = Str::random(64);
        $user->verification_code = $verificationCode;
        $user->save();

        ApiLogger::logAuth('forgot_password_success', $email, true, $user->id);

        $resetLink = url('/user/resetpassword') . '/' . $userType . '/' . $verificationCode;

        // CI parity: Mailsmsconf forgot_password branch sends the link by mail
        // when a template is configured. Never fail the request when SMTP is down.
        try {
            $recipient = $userType === 'parent'
                ? $this->parentGuardianEmail($user->id)
                : $this->studentEmail($user->user_id);

            if ($recipient) {
                Mail::raw(
                    "Use the following link to recover your password: {$resetLink}",
                    function ($message) use ($recipient) {
                        $message->to($recipient)->subject('Recover your password');
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Forgot password mail failed: ' . $e->getMessage());
        }

        // Dev aid: local mail drivers (mailpit/log) may not be read; keep the
        // link in the server log so the flow stays testable end-to-end.
        Log::info('Password reset link generated', ['email' => $email, 'link' => $resetLink]);

        $data = [];
        if (config('app.debug')) {
            $data['reset_link'] = $resetLink;
            $data['verification_code'] = $verificationCode;
        }

        // CI flash line: please_check_your_email_to_recover_your_password
        return $this->successResponse($data, 'Please check your email to recover your password');
    }

    /**
     * CI parity: Site::resetpassword($role, $verification_code) — final step.
     * Validates the code with the same joins as CI (getUserValidCode /
     * getParentUserValidCode), updates users.password and clears the code
     * (CI: saveNewPass sets verification_code to "").
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userType = $validated['user_type'];
        $code = trim((string) $validated['verification_code']);
        $newPassword = $validated['password'];

        $user = $this->findUserByResetCode($userType, $code);

        if (!$user) {
            // CI flash line: invalid_link (redirects back to site/ufpassword)
            return $this->errorResponse('Invalid link', null, 400);
        }

        DB::transaction(function () use ($user, $newPassword) {
            // CI stores the user-flow password as plaintext (same value login compares).
            $user->password = $newPassword;
            $user->verification_code = '';
            $user->save();
            $user->tokens()->delete();
        });

        ApiLogger::logAuth('password_reset_success', (string) $user->username, true, $user->id);

        // CI flash line: password_reset_successfully
        return $this->successResponse(null, 'Password reset successfully');
    }

    /**
     * CI parity with User_model::checkLogin() student branch:
     * users JOIN students ON students.id = users.user_id
     * WHERE username OR admission_no/mobileno/email (per sch_settings.student_login).
     */
    private function findStudentLogin(string $username, string $password, array $methods): ?User
    {
        $query = User::query()
            ->join('students as s', 's.id', '=', 'users.user_id')
            ->select('users.*')
            ->where(function ($q) use ($username, $methods) {
                $q->where('users.username', $username);
                if (in_array('admission_no', $methods, true)) {
                    $q->orWhere('s.admission_no', $username);
                }
                if (in_array('mobile_number', $methods, true)) {
                    $q->orWhere('s.mobileno', $username);
                }
                if (in_array('email', $methods, true)) {
                    $q->orWhere('s.email', $username);
                }
            })
            ->limit(1);

        $user = $query->first();
        if ($user && $user->password === $password) {
            return $user;
        }

        return null;
    }

    /**
     * CI parity with User_model::checkLogin() parent branch:
     * users JOIN students ON students.parent_id = users.id
     * WHERE username OR guardian_phone/guardian_email (per sch_settings.parent_login).
     */
    private function findParentLogin(string $username, string $password, array $methods): ?User
    {
        $query = User::query()
            ->join('students as s', 's.parent_id', '=', 'users.id')
            ->select('users.*')
            ->where(function ($q) use ($username, $methods) {
                $q->where('users.username', $username);
                if (in_array('mobile_number', $methods, true)) {
                    $q->orWhere('s.guardian_phone', $username);
                }
                if (in_array('email', $methods, true)) {
                    $q->orWhere('s.guardian_email', $username);
                }
            })
            ->limit(1);

        $user = $query->first();
        if ($user && $user->password === $password) {
            return $user;
        }

        return null;
    }

    /**
     * CI parity with User_model::forgotPassword() + getUserByEmail/getParentByEmail:
     * student -> students.email (users.user_id = students.id, role student)
     * parent  -> students.guardian_email (students.parent_id = users.id, role parent)
     */
    private function findUserByResetEmail(string $userType, string $email): ?User
    {
        if ($userType === 'student') {
            return User::query()
                ->join('students as s', 's.id', '=', 'users.user_id')
                ->select('users.*')
                ->where('users.role', 'student')
                ->where('s.email', $email)
                ->where('s.email', '!=', '')
                ->limit(1)
                ->first();
        }

        return User::query()
            ->join('students as s', 's.parent_id', '=', 'users.id')
            ->select('users.*')
            ->where('users.role', 'parent')
            ->where('s.guardian_email', $email)
            ->where('s.guardian_email', '!=', '')
            ->limit(1)
            ->first();
    }

    /**
     * CI parity with User_model::getUserByCodeUsertype() + getUserValidCode/
     * getParentUserValidCode(): same joins, matched on users.verification_code.
     */
    private function findUserByResetCode(string $userType, string $code): ?User
    {
        if ($code === '') {
            return null;
        }

        if ($userType === 'student') {
            return User::query()
                ->join('students as s', 's.id', '=', 'users.user_id')
                ->select('users.*')
                ->where('users.role', 'student')
                ->where('users.verification_code', $code)
                ->limit(1)
                ->first();
        }

        return User::query()
            ->join('students as s', 's.parent_id', '=', 'users.id')
            ->select('users.*')
            ->where('users.role', 'parent')
            ->where('users.verification_code', $code)
            ->limit(1)
            ->first();
    }

    private function studentEmail(int $studentId): ?string
    {
        $email = Student::where('id', $studentId)->value('email');
        return $email ?: null;
    }

    private function parentGuardianEmail(int $userId): ?string
    {
        $email = Student::where('parent_id', $userId)->value('guardian_email');
        return $email ?: null;
    }
}
