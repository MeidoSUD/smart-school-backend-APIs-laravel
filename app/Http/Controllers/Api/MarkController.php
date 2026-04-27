<?php

namespace App\Http\Controllers\Api;

 
use App\Models\ExamSchedule;
use App\Models\ExamResult;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Mark.php
 */
class MarkController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('MarkController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $reportcard = ExamSchedule::where('session_id', $studentSession->session_id)
            ->where('class_id', $studentSession->class_id)
            ->get();
        
        $examSchedule = [];
        if ($reportcard->isNotEmpty()) {
            foreach ($reportcard as $data) {
                $examResults = ExamResult::where('exam_schedule_id', $data->id)
                    ->where('student_session_id', $studentSession->id)
                    ->get();
                
                $examArray = [
                    'exam_id' => $data->id,
                    'full_marks' => $data->full_marks,
                    'passing_marks' => $data->passing_marks,
                    'exam_name' => $data->name ?? 'Exam',
                    'get_marks' => $examResults->first()->mark_obtained ?? null,
                ];
                
                $examSchedule[] = $examArray;
                }


            }


        
        $data = [
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'examSchedule' => $examSchedule,
        ];
        
        return $this->successResponse($data);
        }



    public function marklist(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $student = Student::find($studentSession->student_id);
        
        $gradeList = Grade::where('is_active', 'yes')->get();
        
        $examList = ExamSchedule::where('session_id', $studentSession->session_id)
            ->where('class_id', $studentSession->class_id)
            ->get();
        
        $examSchedule = [];
        foreach ($examList as $ex) {
            $examSub = ExamResult::where('exam_schedule_id', $ex->id)
                ->where('student_session_id', $studentSession->id)
                ->get();
            
            $examArray = [
                'exam_name' => $ex->name ?? 'Exam',
                'exam_result' => $examSub,
            ];
            
            $examSchedule[] = $examArray;
            }


        
        $data = [
            'title' => 'Student Details',
            'gradeList' => $gradeList,
            'examSchedule' => $examSchedule,
            'student' => $student,
        ];
        
        return $this->successResponse($data);
        }



    public function view($id): JsonResponse
    {
        $mark = ExamResult::find($id);
        
        if (!$mark) {
            return $this->errorResponse('Mark not found', null, 404);
            }


        
        return $this->successResponse(['mark' => $mark]);
        }



    private function getStudentSession($user)
    {
        $studentId = null;
        
        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
            }


        
        if (!$studentId) {
            return null;
            }


        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
        }


    }
