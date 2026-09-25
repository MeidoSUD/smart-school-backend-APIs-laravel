<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Exam;
use Modules\Academic\Entities\ExamSchedule;
use Modules\Academic\Entities\ExamResult;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Grade;
use Modules\Finance\Entities\FeeCategory;
use Modules\Core\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('MarkController');
    }

    public function index(Request $request): JsonResponse
    {
        // CI source: api/user/Mark.php index() returns nested structure with
        // title, examlist, classlist, feecategorylist, class_id, section_id and
        // examSchedule {status, result:[student + exam_array]}.
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $classId = $studentSession->class_id;
        $sectionId = $studentSession->section_id;
        $sessionId = $studentSession->session_id;

        $examList = Exam::orderBy('id')->get();
        $classList = Classe::orderBy('id')->get();
        $feeCategoryList = FeeCategory::orderBy('id')->get();

        $reportcard = ExamSchedule::getExamsByClassAndSection($classId, $sectionId, $sessionId);

        $data = [
            'title' => 'Exam Marks',
            'exam_id' => '',
            'class_id' => $classId,
            'section_id' => $sectionId,
            'examlist' => $examList,
            'classlist' => $classList,
            'feecategorylist' => $feeCategoryList,
            'examSchedule' => ['status' => 'no'],
        ];

        if ($reportcard->isNotEmpty()) {
            $examId = $reportcard->first()->exam_id ?? null;

            $examScheduleDetail = DB::table('exam_schedules')
                ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
                ->join('exams', 'exams.id', '=', 'exam_schedules.exam_id')
                ->join('class_sections', 'class_sections.id', '=', 'teacher_subjects.class_section_id')
                ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
                ->where('class_sections.class_id', $classId)
                ->where('class_sections.section_id', $sectionId)
                ->when($examId, fn($q) => $q->where('exam_schedules.exam_id', $examId))
                ->where('exam_schedules.session_id', $sessionId)
                ->select(
                    'exam_schedules.id',
                    'exam_schedules.exam_id',
                    'exam_schedules.full_marks',
                    'exam_schedules.passing_marks',
                    'subjects.name',
                    'subjects.type'
                )
                ->get();

            $studentList = DB::table('students')
                ->join('student_session', 'student_session.student_id', '=', 'students.id')
                ->where('student_session.class_id', $classId)
                ->where('student_session.section_id', $sectionId)
                ->where('student_session.session_id', $sessionId)
                ->select('students.id', 'students.firstname', 'students.lastname', 'students.admission_no', 'students.dob', 'students.father_name')
                ->get();

            if ($examScheduleDetail->isNotEmpty()) {
                $newArray = [];
                $status = 'yes';
                foreach ($studentList as $stu) {
                    $row = [
                        'student_id' => $stu->id,
                        'firstname' => $stu->firstname,
                        'lastname' => $stu->lastname,
                        'admission_no' => $stu->admission_no,
                        'dob' => $stu->dob,
                        'father_name' => $stu->father_name,
                    ];
                    $x = [];
                    foreach ($examScheduleDetail as $ex) {
                        $examArray = [
                            'exam_schedule_id' => $ex->id,
                            'exam_id' => $ex->exam_id,
                            'full_marks' => $ex->full_marks,
                            'passing_marks' => $ex->passing_marks,
                            'exam_name' => $ex->name,
                            'exam_type' => $ex->type,
                        ];
                        $result = ExamResult::where('exam_schedule_id', $ex->id)
                            ->where('student_id', $stu->id)
                            ->first();
                        if (empty($result)) {
                            $status = 'no';
                        } else {
                            $examArray['attendence'] = $result->attendence;
                            $examArray['get_marks'] = $result->get_marks;
                        }
                        $x[] = $examArray;
                    }
                    $row['exam_array'] = $x;
                    $newArray[] = $row;
                }
                $data['examSchedule'] = ['status' => $status, 'result' => $newArray];
            }
        }

        return $this->successResponse($data);
    }

    public function marklist(Request $request): JsonResponse
    {
        // CI source: api/user/Mark.php marklist() keys: title, gradeList,
        // student_due_fee, transport_fee, examSchedule [{exam_name, exam_result:
        // [{exam_schedule_id, exam_id, full_marks, passing_marks, exam_name,
        // exam_type, attendence, get_marks}]}], student.
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);

        $gradeList = Grade::where('is_active', 'yes')->get();

        $studentDueFee = $this->getDueFeeByStudent(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->student_id,
            $studentSession->session_id
        );

        $transportFee = DB::table('transport_feemaster')
            ->leftJoin('student_transport_fees', function ($join) use ($studentSession) {
                $join->on('transport_feemaster.id', '=', 'student_transport_fees.transport_feemaster_id')
                    ->where('student_transport_fees.student_session_id', '=', $studentSession->id);
            })
            ->where('transport_feemaster.session_id', $studentSession->session_id)
            ->orderBy('transport_feemaster.id')
            ->select('transport_feemaster.*', 'student_transport_fees.id as student_transport_fee_id')
            ->get();

        $examList = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $examSchedule = [];
        foreach ($examList as $exam) {
            $rows = ExamSchedule::getResultsByStudentAndExam(
                $exam->exam_id,
                $studentSession->student_id,
                $studentSession->session_id
            );

            $x = [];
            foreach ($rows as $value) {
                $x[] = [
                    'exam_schedule_id' => $value->exam_schedule_id,
                    'exam_id' => $value->exam_id,
                    'full_marks' => $value->full_marks,
                    'passing_marks' => $value->passing_marks,
                    'exam_name' => $value->name,
                    'exam_type' => $value->type,
                    'attendence' => $value->attendence,
                    'get_marks' => $value->get_marks,
                ];
            }

            $examSchedule[] = [
                'exam_name' => $exam->name ?? 'Exam',
                'exam_result' => $x,
            ];
        }

        $data = [
            'title' => 'Student Details',
            'gradeList' => $gradeList,
            'student_due_fee' => $studentDueFee,
            'transport_fee' => $transportFee,
            'examSchedule' => $examSchedule,
            'student' => $student,
        ];

        return $this->successResponse($data);
    }

    public function view($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $mark = ExamResult::where('id', $id)
            ->where('student_id', $studentSession->student_id)
            ->first();

        if (!$mark) {
            return $this->errorResponse('Mark not found', null, 404);
        }

        return $this->successResponse(['mark' => $mark]);
    }

    private function getDueFeeByStudent(int $classId, int $sectionId, int $studentId, int $sessionId): array
    {
        // Mirrors Studentfee_model::getDueFeeBystudent().
        $sql = "SELECT feemasters.id as feemastersid, feemasters.amount as amount,"
            . "IFNULL(student_fees.id, 'xxx') as invoiceno,"
            . "IFNULL(student_fees.amount_discount, 'xxx') as discount,"
            . "IFNULL(student_fees.amount_fine, 'xxx') as fine,"
            . "IFNULL(student_fees.payment_mode, 'xxx') as payment_mode,"
            . "IFNULL(student_fees.date, 'xxx') as date,"
            . "feetype.type, feecategory.category, student_fees.description "
            . "FROM feemasters LEFT JOIN (select student_fees.id,student_fees.feemaster_id,"
            . "student_fees.payment_mode,student_fees.amount_fine,student_fees.amount_discount,"
            . "student_fees.date,student_fees.student_session_id,student_fees.description "
            . "from student_fees, student_session where "
            . "student_fees.student_session_id=student_session.id and student_session.student_id=? "
            . "and student_session.class_id=? and student_session.section_id=?) as student_fees "
            . "ON student_fees.feemaster_id=feemasters.id "
            . "JOIN feetype ON feemasters.feetype_id = feetype.id "
            . "JOIN feecategory ON feetype.feecategory_id = feecategory.id "
            . "where feemasters.class_id=? and feemasters.session_id=?";

        return DB::select($sql, [$studentId, $classId, $sectionId, $classId, $sessionId]);
    }
}
