<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\SubjectGroupSubject;
use App\Models\SubjectGroupClassSection;
use App\Models\TeacherSubject;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Subject.php
 */
class SubjectController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('SubjectController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $subjects = SubjectGroupClassSection::where('class_section_id', $studentSession->class_id)
            ->where('session_id', $studentSession->session_id)
            ->with('subjectGroup.subjects')
            ->get();
        
        $subjectList = [];
        foreach ($subjects as $sgcs) {
            if ($sgcs->subjectGroup && $sgcs->subjectGroup->subjects) {
                foreach ($sgcs->subjectGroup->subjects as $subject) {
                    $subjectList[] = [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'type' => $subject->type,
                        'code' => $subject->code,
                    ];
                    }


                }


            }


        
        return $this->successResponse(['subjects' => $subjectList]);
        }



    public function view($id): JsonResponse
    {
        $subject = Subject::find($id);
        
        if (!$subject) {
            return $this->errorResponse('Subject not found', null, 404);
            }


        
        return $this->successResponse(['subject' => $subject]);
        }



    public function getSubjctByClassandSection(Request $request): JsonResponse
    {
        $classId = $request->get('class_id') ?? $request->post('class_id');
        $sectionId = $request->get('section_id') ?? $request->post('section_id');
        
        if (!$classId || !$sectionId) {
            return $this->errorResponse('class_id and section_id are required');
            }


        
        $classSection = DB::table('class_sections')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();
        
        if (!$classSection) {
            return $this->errorResponse('Class section not found');
            }


        
        $currentSession = Setting::where('is_active', 1)->first();
        
        $subjects = DB::table('teacher_subjects')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->join('staff', 'staff.id', '=', 'teacher_subjects.teacher_id')
            ->where('teacher_subjects.class_section_id', $classSection->id)
            ->when($currentSession, fn($q) => $q->where('teacher_subjects.session_id', $currentSession->id))
            ->select('teacher_subjects.*', 'subjects.name', 'subjects.type', 'subjects.code', 'staff.name as teacher_name', 'staff.surname')
            ->get();
        
        return $this->successResponse(['subjects' => $subjects]);
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
