<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Exam;
use Modules\Academic\Entities\ExamSchedule;
use Modules\Academic\Entities\ExamGroupStudent;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\MarksDivision;
use Modules\Academic\Entities\Grade;
use Modules\Finance\Entities\FeeType;
use Modules\Finance\Entities\FeeMaster;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ExamController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examResult = ExamSchedule::getExamsByClassAndSection(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $data = [
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
            'examlist' => $examResult,
        ];

        return $this->successResponse($data);
    }

    public function view($id): JsonResponse
    {
        $exam = Exam::find($id);

        if (!$exam) {
            return $this->errorResponse('Exam not found', null, 404);
        }

        return $this->successResponse(['exam' => $exam]);
    }

    public function getByFeecategory(Request $request): JsonResponse
    {
        // CI source: api/user/Exam.php getByFeecategory uses feetype by feecategory_id.
        // CI Feetype_model has no getTypeByFeecategory; intended table is `feetype`.
        $feecategoryId = $request->get('feecategory_id');

        $data = FeeType::where('feecategory_id', $feecategoryId)
            ->orderBy('id')
            ->get();

        return $this->successResponse($data);
    }

    public function getStudentCategoryFee(Request $request): JsonResponse
    {
        // CI source: api/user/Exam.php getStudentCategoryFee uses
        // Feemaster_model::getTypeByFeecategory($type, $class_id) which queries
        // feemasters JOIN classes JOIN feetype scoped to current session.
        // CI api_success() ignores the 3rd arg, so return data only (G-4.1 fix:
        // never pass 'fail'/'success' string as int status code).
        $type = $request->post('type', $request->get('type'));
        $classId = $request->post('class_id', $request->get('class_id'));

        $setting = Setting::where('is_active', 'yes')->first();
        $sessionId = $setting ? $setting->session_id : null;

        $query = DB::table('feemasters')
            ->join('classes', 'feemasters.class_id', '=', 'classes.id')
            ->join('feetype', 'feemasters.feetype_id', '=', 'feetype.id')
            ->select(
                'feemasters.id',
                'feemasters.session_id',
                'feemasters.amount',
                'feemasters.description',
                'classes.class',
                'feetype.type'
            )
            ->where('feemasters.class_id', $classId)
            ->where('feemasters.feetype_id', $type)
            ->when($sessionId, fn($q) => $q->where('feemasters.session_id', $sessionId))
            ->orderBy('feemasters.id');

        $data = $query->first();

        return $this->successResponse($data);
    }

    public function examSearch(Request $request): JsonResponse
    {
        $data = ['title' => 'Search exam', 'exp_title' => 'Exam Result'];

        if ($request->isMethod('post')) {
            $search = $request->post('search');

            if ($search === 'search_filter') {
                $dateFrom = $request->post('date_from');
                $dateTo = $request->post('date_to');

                $resultList = Exam::whereBetween('created_at', [$dateFrom, $dateTo])->get();
                $data['exp_title'] = 'Exam Result From ' . $dateFrom . ' To ' . $dateTo;
                $data['resultList'] = $resultList;
            } else {
                $searchText = $request->post('search_text');
                $resultList = Exam::where('name', 'like', '%' . $searchText . '%')->get();
                $data['resultList'] = $resultList;
            }
        }

        return $this->successResponse($data);
    }

    public function examresult(Request $request): JsonResponse
    {
        // CI source: api/user/Exam.php examresult returns marks_division,
        // exam_result (searchStudentExams) and exam_grade (getGradeDetails).
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examResult = ExamGroupStudent::where('student_session_id', $studentSession->id)
            ->with('examGroup')
            ->get();

        $marksDivision = MarksDivision::orderBy('id')->get();

        $data = [
            'marks_division' => $marksDivision,
            'exam_result' => $examResult,
            'exam_grade' => $this->getGradeDetails(),
        ];

        return $this->successResponse($data);
    }

    private function getGradeDetails(): array
    {
        // Mirrors Grade_model::getGradeDetails(): group grades by exam_type.
        $types = Grade::select('exam_type')->distinct()->orderBy('exam_type')->pluck('exam_type');

        $details = [];
        foreach ($types as $examType) {
            $details[] = [
                'exam_key' => $examType,
                'exm_type_value' => $examType,
                'exam_grade_values' => Grade::where('exam_type', $examType)->orderBy('id')->get(),
            ];
        }

        return $details;
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

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->session_id))
            ->first();
    }
}
