<?php

namespace Modules\Staff\Http\Controllers\Api;

use Modules\Staff\Entities\Staff;
use Modules\Staff\Entities\StaffRating;
use Modules\Academic\Entities\ClassSection;
use Modules\Academic\Entities\TeacherSubject;
use Modules\Core\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class TeacherController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('TeacherController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $teachers = DB::table('teacher_subjects')
            ->join('staff', 'staff.id', '=', 'teacher_subjects.teacher_id')
            ->where('teacher_subjects.class_section_id', $studentSession->id)
            ->select('staff.*')
            ->distinct()
            ->get();
        
        $teacherList = $teachers->values();

        $genderList = ['Male', 'Female', 'Other'];
        
        $userRatedStaffList = StaffRating::where('user_id', $user->id)
            ->where('role', $user->role)
            ->get();
        
        $getRatingByStudent = StaffRating::where('user_id', $user->id)
            ->where('role', 'student')
            ->get();
        
        $reviews = [];
        $comments = [];
        foreach ($getRatingByStudent as $value) {
            $reviews[$value->staff_id] = $value->rate;
            $comments[$value->staff_id] = $value->comment;
            }


        
        $data = [
            'title' => 'Teachers',
            'teachers' => $teacherList,
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'user_id' => $user->id,
            'role' => $user->role,
            'teacherlist' => $teacherList,
            'genderList' => $genderList,
            'user_ratedstafflist' => $userRatedStaffList,
            'reviews' => $reviews,
            'comment' => $comments,
        ];
        
        return $this->successResponse($data);
        }



    public function getSubjctByClassandSection(Request $request): JsonResponse
    {
        $classId = $request->post('class_id');
        $sectionId = $request->post('section_id');
        
        $classSection = ClassSection::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();
        
        if (!$classSection) {
            return $this->errorResponse('Class section not found');
            }


        
        $subjects = DB::table('teacher_subjects')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_subjects.class_section_id', $classSection->id)
            ->select('subjects.*')
            ->get();
        
        return $this->successResponse(['subjects' => $subjects]);
        }



    public function getSubjectTeachers(Request $request): JsonResponse
    {
        $classId = $request->post('class_id');
        $sectionId = $request->post('section_id');
        
        $classSection = ClassSection::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();
        
        if (!$classSection) {
            return $this->errorResponse('Class section not found');
            }


        
        $teachers = DB::table('teacher_subjects')
            ->join('staff', 'staff.id', '=', 'teacher_subjects.teacher_id')
            ->where('teacher_subjects.class_section_id', $classSection->id)
            ->select('staff.*')
            ->distinct()
            ->get();
        
        return $this->successResponse(['teachers' => $teachers]);
        }



    public function view($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $isAssignedTeacher = TeacherSubject::where('teacher_id', $id)
            ->where('class_section_id', $studentSession->id)
            ->exists();

        if (!$isAssignedTeacher) {
            return $this->errorResponse('Teacher not found for your class', null, 404);
        }

        $teacher = Staff::find($id);
        
        if (!$teacher) {
            return $this->errorResponse('Teacher not found', null, 404);
        }

        return $this->successResponse(['teacher' => $teacher]);
    }



    public function rating(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required',
            'comment' => 'required|string',
            'rate' => 'required|numeric|min:1|max:5',
        ]);
        
        $user = $request->user();
        
        StaffRating::updateOrCreate(
            ['staff_id' => $request->staff_id, 'user_id' => $user->id, 'role' => $user->role],
            [
                'comment' => $request->comment,
                'rate' => $request->rate,
                'status' => 1,
            ]
        );
        
        return $this->successResponse(null, 'Rating saved successfully');
    }
}
