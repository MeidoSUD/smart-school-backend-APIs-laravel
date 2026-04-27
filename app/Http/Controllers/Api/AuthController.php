<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use App\Models\StudentSession;
use App\Models\Setting;
use App\Models\Classe;
use App\Models\Section;
use App\Models\StudentFeeMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ApiLogger;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('AuthController');
    }

    /**
     * Login user and generate token
     *
     * CodeIgniter Route: POST /api/auth/login
     * Laravel Route: POST /api/auth/login
     *
     * @param LoginRequest $request Validated login credentials
     * @return JsonResponse
     */
public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Log the login attempt
        ApiLogger::logAuth('login_attempt', $credentials['username'], false);

        // Find user by username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            ApiLogger::logAuth('login_failed', $credentials['username'], false);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            ApiLogger::logAuth('login_disabled', $credentials['username'], false, $user->id);
            return $this->errorResponse('Your account is disabled, please contact administrator.', null, 403);
        }

        // Validate plain text password (legacy compatibility)
        if ($user->password !== $credentials['password']) {
            ApiLogger::logAuth('login_failed', $credentials['username'], false, $user->id);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        // Generate secure token
        $token = Str::random(64);

        // Save token to user
        $user->token = $token;
        $user->save();

        // Log successful login
        ApiLogger::logAuth('login_success', $credentials['username'], true, $user->id);

        // Get user profile data based on role
        $userData = $this->buildUserResponse($user);

        // ═══════════════════════════════════════════════════════════════════════════
        // CODEIGNITER EQUIVALENT:
        // $response_data = ['token' => $token, 'user' => $result];
        // return $this->api_success($response_data, 'Login successful');
        // ═══════════════════════════════════════════════════════════════════════════

        return $this->successResponse([
            'token' => $token,
            'user' => $userData,
        ], 'Login successful');
    }

    /**
     * Logout user and clear token
     *
     * CodeIgniter Route: POST /api/auth/logout
     * Laravel Route: POST /api/auth/logout
     *
     * @param Request $request
     * @return JsonResponse
     */
public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $userId = $user->id;
            $username = $user->username;
            
            // Clear the token
            $user->token = null;
            $user->save();
            
            // Log logout
            ApiLogger::logAuth('logout', $username, true, $userId);

            return $this->successResponse(null, 'Logged out successfully');
        }

        return $this->errorResponse('Not logged in', null, 401);
    }

   /**
     * Change user password
     *
     * CodeIgniter Route: POST /api/auth/changepass
     * Laravel Route: POST /api/auth/changepass
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // CODEIGNITER EQUIVALENT:
        // $role = $this->result["role"];
        // if ($role == 'guest') { return error; }
        // ═══════════════════════════════════════════════════════════════════════════

        if ($user->role === 'guest') {
            return $this->errorResponse('Guest users cannot change password here');
        }

        $validated = $request->validated();

        // ═══════════════════════════════════════════════════════════════════════════
        // CODEIGNITER EQUIVALENT:
        // $query1 = $this->user_model->checkOldPass($data_array);
        // ═══════════════════════════════════════════════════════════════════════════

        if ($user->password !== $validated['current_pass']) {
            return $this->errorResponse('Invalid current password');
        }

        // Update password (plain text - no hashing for legacy compatibility)
        $user->password = $validated['new_pass'];
        $user->save();

        return $this->successResponse(null, 'Password changed successfully');
    }

    /**
     * Build user response data based on role
     *
     * @param User $user
     * @return array
     */
    private function buildUserResponse(User $user): array
    {
        $data = $user->toArray();
        unset($data['password']); // Remove password from response

        // ═══════════════════════════════════════════════════════════════════════════
        // CODEIGNITER EQUIVALENT:
        // if ($user->role == "student") {
        //     $result = $this->user_model->read_user_information($user->id);
        //     $defaultclass = $this->user_model->get_studentdefaultClass($user->id);
        //     $result->class_id = $defaultclass['class_id'];
        //     ...
        // }
        // ═══════════════════════════════════════════════════════════════════════════

        switch ($user->role) {
            case 'student':
                $student = Student::find($user->user_id);
                if ($student) {
                    $data = array_merge($data, $student->toArray());

                    // Get default class/section
                    $studentSession = $this->getStudentDefaultSession($student->id);
                    if ($studentSession) {
                        $data['class_id'] = $studentSession->class_id;
                        $data['section_id'] = $studentSession->section_id;
                        $data['student_session_id'] = $studentSession->id;

                        // Add class and section names
                        $class = Classe::find($studentSession->class_id);
                        $section = Section::find($studentSession->section_id);
                        $data['class'] = $class ? $class->class : null;
                        $data['section'] = $section ? $section->section : null;
                        
                        // Add fee summary
                        $data['fees'] = $this->getStudentFeeSummary($studentSession->id);
                    }
                }
                break;

            case 'parent':
                // Get first child info for parent
                $child = Student::where('parent_id', $user->id)->first();
                if ($child) {
                    $studentSession = $this->getStudentDefaultSession($child->id);
                    if ($studentSession) {
                        $data['student_id'] = $child->id;
                        $data['class_id'] = $studentSession->class_id;
                        $data['section_id'] = $studentSession->section_id;
                        $data['student_session_id'] = $studentSession->id;
                        
                        // Add class and section names
                        $class = Classe::find($studentSession->class_id);
                        $section = Section::find($studentSession->section_id);
                        $data['class'] = $class ? $class->class : null;
                        $data['section'] = $section ? $section->section : null;
                        
                        // Add fee summary
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

    /**
     * Get student's default/current session
     *
     * @param int $studentId
     * @return StudentSession|null
     */
    private function getStudentDefaultSession(int $studentId): ?StudentSession
    {
        // Try current active session first
        $setting = Setting::where('is_active', 1)->first();

        $query = StudentSession::where('student_id', $studentId);

        if ($setting) {
            $query->where('session_id', $setting->id);
        }

        $studentSession = $query->first();

        // Fallback to default login
        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first();
        }

        // Fallback to most recent
        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return $studentSession;
    }

    /**
     * Get student's fee summary
     * 
     * @param int $studentSessionId
     * @return array
     */
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