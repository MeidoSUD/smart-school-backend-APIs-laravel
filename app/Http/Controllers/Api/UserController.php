<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentSession;
use App\Models\Setting;
use App\Models\AttendenceType;
use App\Models\Classe;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Get user dashboard data
     *
     * GET /api/user/dashboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        // Get student session info based on role
        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // Get school settings
        $setting = $this->getSchoolSettings();

        // Calculate session dates
        $sessionDetails = $this->getSessionDates($setting);

        // Get attendance data
        $data['attendence_percentage'] = $this->calculateAttendancePercentage(
            $studentSession->id,
            $sessionDetails['start'],
            $sessionDetails['end']
        );

        // Get current class info
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        // Get student details
        $student = Student::find($studentSession->student_id);

        // Add student info to response
        $data['studentsession_username'] = $user->username;
        $data['student_data'] = [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'student_id' => $studentSession->student_id,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
        ];

        // Get settings info
        $settings = Setting::first();
        $data['low_attendance_limit'] = $settings ? ($settings->low_attendance_limit ?? 75) : 75;

        return $this->successResponse($data);
    }

    /**
     * Get user profile
     *
     * GET /api/user/profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        // Get school settings
        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;

        // Get student session info
        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // Get full student details
        $student = Student::with(['category'])
            ->find($studentSession->student_id);

        if (!$student) {
            return $this->errorResponse('Student not found');
        }

        // Get class and section
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        // Build profile data
        $data['student'] = [
            'id' => $student->id,
            'admission_no' => $student->admission_no,
            'roll_no' => $student->roll_no,
            'firstname' => $student->firstname,
            'middlename' => $student->middlename,
            'lastname' => $student->lastname,
            'fullname' => $student->firstname . ' ' . $student->lastname,
            'gender' => $student->gender,
            'dob' => $student->dob,
            'religion' => $student->religion,
            'email' => $student->email,
            'mobileno' => $student->mobileno,
            'admission_date' => $student->admission_date,
            'image' => $student->image,
            'father_name' => $student->father_name,
            'father_phone' => $student->father_phone,
            'mother_name' => $student->mother_name,
            'mother_phone' => $student->mother_phone,
            'guardian_name' => $student->guardian_name,
            'guardian_phone' => $student->guardian_phone,
            'guardian_relation' => $student->guardian_relation,
            'guardian_address' => $student->guardian_address,
            'current_address' => $student->local_address ?? $student->permanent_address ?? '',
            'category' => $student->category ? $student->category->category : null,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
        ];

        // Add user role info
        $data['role'] = $user->role;

        return $this->successResponse($data);
    }

    /**
     * Get user fees
     *
     * GET /api/user/fees
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        // Get student session
        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // Get school settings
        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;

        // Get student details
        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $data['student'] = [
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
        ];

        $data['payment_method'] = false; // Configure based on your payment settings

        return $this->successResponse($data);
    }

    /**
     * Get detailed fees
     *
     * GET /api/user/getfees
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getfees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        // Get student session
        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // Get school settings
        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;
        $data['adm_auto_insert'] = $setting ? $setting->adm_auto_insert : false;

        // Get student details
        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $data['student'] = [
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
        ];

        $data['payment_method'] = false; // Configure based on your payment settings
        $data['student_due_fee'] = [];
        $data['transport_fees'] = [];
        $data['student_discount_fee'] = [];

        return $this->successResponse($data);
    }

    /**
     * Get current student session based on user role
     *
     * @param User $user
     * @return StudentSession|null
     */
    private function getStudentSession($user): ?StudentSession
    {
        $studentId = null;

        switch ($user->role) {
            case 'student':
                $studentId = $user->user_id;
                break;
            case 'parent':
                // Get first child for parent
                $firstChild = Student::where('parent_id', $user->id)->first();
                $studentId = $firstChild ? $firstChild->id : null;
                break;
            default:
                return null;
        }

        if (!$studentId) {
            return null;
        }

        // Get current session
        $currentSession = $this->getCurrentSession();

        // Try to find session for current academic year
        $studentSession = StudentSession::where('student_id', $studentId)
            ->where('session_id', $currentSession ? $currentSession->id : null)
            ->first();

        // If not found, get default login session
        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first();
        }

        // If still not found, get most recent session
        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return $studentSession;
    }

    /**
     * Get current active session
     *
     * @return Session|null
     */
    private function getCurrentSession()
    {
        // Assuming session table exists and has is_active field
        return \App\Models\Session::where('is_active', 1)->first();
    }

    /**
     * Get school settings
     *
     * @return Setting|null
     */
    private function getSchoolSettings(): ?Setting
    {
        return Setting::first();
    }

    /**
     * Get session dates for attendance calculation
     *
     * @param Setting|null $setting
     * @return array
     */
    private function getSessionDates(?Setting $setting): array
    {
        $startMonth = $setting ? ($setting->start_month ?? 4) : 4;

        $currentYear = date('Y');
        $start = Carbon::createFromDate($currentYear, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($currentYear, $startMonth, 1)->addYear()->endOfMonth();

        // If we're before the start month, adjust to previous year
        if (date('n') < $startMonth) {
            $start = $start->subYear();
            $end = $end->subYear();
        }

        return [
            'start' => $start->toDateString(),
            'end' => min($end->toDateString(), date('Y-m-d')),
        ];
    }

    /**
     * Calculate student attendance percentage
     *
     * @param int $studentSessionId
     * @param string $start
     * @param string $end
     * @return float
     */
    private function calculateAttendancePercentage(int $studentSessionId, string $start, string $end): float
    {
        // This is a placeholder - implement based on your attendance table structure
        // You would typically query your attendence table here
        // For now, return -1 to indicate no data
        return -1.0;
    }
}