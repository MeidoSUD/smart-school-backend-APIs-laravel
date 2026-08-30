<?php

namespace Modules\Staff\Http\Controllers\Api;

use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Entities\ClassSection;
use Modules\Academic\Entities\TeacherSubject;
use Modules\Core\Entities\Setting;
use Modules\Core\Http\Controllers\Api\Controller;
use Modules\Core\Services\StudentSessionService;
use Modules\Staff\Entities\Staff;
use Modules\Staff\Entities\StaffRating;

class TeacherController extends Controller
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

        if (! $studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $classId = $studentSession->class_id;
        $sectionId = $studentSession->section_id;

        $teachers = DB::table('subject_timetable')
            ->join('subject_group_subjects', 'subject_timetable.subject_group_subject_id', '=', 'subject_group_subjects.id')
            ->leftJoin('subjects', 'subject_group_subjects.subject_id', '=', 'subjects.id')
            ->join('staff', 'staff.id', '=', 'subject_timetable.staff_id')
            ->leftJoin('classes', 'classes.id', '=', 'subject_timetable.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'subject_timetable.section_id')
            ->leftJoin('class_teacher', function ($join) {
                $join->on('class_teacher.class_id', '=', 'classes.id')
                    ->on('class_teacher.staff_id', '=', 'staff.id')
                    ->on('class_teacher.section_id', '=', 'sections.id');
            })
            ->where('subject_timetable.class_id', $classId)
            ->where('subject_timetable.section_id', $sectionId)
            ->where('subject_timetable.session_id', $this->getCurrentSession())
            ->where('staff.is_active', '1')
            ->select(
                'staff.*',
                'subject_group_subjects.subject_id',
                'subjects.name as subject_name',
                'subjects.code',
                'subjects.type',
                'subject_timetable.time_from',
                'subject_timetable.time_to',
                'subject_timetable.day',
                'subject_timetable.room_no',
                'subject_timetable.start_time',
                'sections.section as section_name',
                'classes.class as class_name',
                DB::raw('CASE WHEN class_teacher.staff_id IS NOT NULL THEN 1 ELSE 0 END as is_class_teacher')
            )
            ->get();

        $teacherList = $teachers->groupBy('id')->map(function ($entries, $staffId) {
            $first = $entries->first();

            return [
                'staff_id' => $staffId,
                'name' => $first->name,
                'surname' => $first->surname,
                'email' => $first->email,
                'contact_no' => $first->contact_no,
                'employee_id' => $first->employee_id,
                'image' => $first->image,
                'gender' => $first->gender,
                'is_class_teacher' => $first->is_class_teacher,
                'subjects' => $entries->map(function ($entry) {
                    return [
                        'subject_id' => $entry->subject_id,
                        'subject_name' => $entry->subject_name,
                        'code' => $entry->code,
                        'type' => $entry->type,
                        'time_from' => $entry->time_from,
                        'time_to' => $entry->time_to,
                        'day' => $entry->day,
                        'room_no' => $entry->room_no,
                        'start_time' => $entry->start_time,
                        'section_name' => $entry->section_name,
                        'class_name' => $entry->class_name,
                    ];
                })->values(),
            ];
        })->values();

        $genderList = ['Male', 'Female', 'Other'];

        $userRatedStaffList = StaffRating::where('user_id', $user->id)
            ->where('role', $user->role)
            ->pluck('staff_id')
            ->toArray();

        $reviews = [];
        $comments = [];
        $avgRate = [];
        $rateCanview = 0;

        if ($user->role === 'student') {
            $getRatingByStudent = StaffRating::where('user_id', $user->id)
                ->where('role', 'student')
                ->get();

            foreach ($getRatingByStudent as $value) {
                $reviews[$value->staff_id] = $value->rate;
                $comments[$value->staff_id] = $value->comment;
            }
        } elseif ($user->role === 'parent') {
            $allRating = StaffRating::where('status', '1')
                ->select('staff_id', DB::raw('SUM(rate) as rate'), DB::raw('COUNT(*) as total'))
                ->groupBy('staff_id')
                ->get();

            foreach ($allRating as $value) {
                if ($value->total >= 3) {
                    $r = $value->rate / $value->total;
                    $avgRate[$value->staff_id] = $r;
                    $rateCanview = 1;
                } else {
                    $avgRate[$value->staff_id] = 0;
                }
                $reviews[$value->staff_id] = $value->total;
            }
        }

        $data = [
            'title' => 'Teachers',
            'teachers' => $teacherList,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'user_id' => $user->id,
            'role' => $user->role,
            'teacherlist' => $teacherList,
            'genderList' => $genderList,
            'user_ratedstafflist' => $userRatedStaffList,
            'reviews' => $reviews,
            'comment' => $comments,
            'avg_rate' => $avgRate,
            'rate_canview' => $rateCanview,
        ];

        return $this->successResponse($data);
    }

    private function getCurrentSession(): ?int
    {
        $setting = Setting::where('is_active', 'yes')->first();

        return $setting?->session_id;
    }

    public function getSubjctByClassandSection(Request $request): JsonResponse
    {
        $classId = $request->post('class_id');
        $sectionId = $request->post('section_id');

        $classSection = ClassSection::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();

        if (! $classSection) {
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

        if (! $classSection) {
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

        if (! $studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $isAssignedTeacher = TeacherSubject::where('teacher_id', $id)
            ->where('class_section_id', $studentSession->id)
            ->exists();

        if (! $isAssignedTeacher) {
            return $this->errorResponse('Teacher not found for your class', null, 404);
        }

        $teacher = Staff::find($id);

        if (! $teacher) {
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
