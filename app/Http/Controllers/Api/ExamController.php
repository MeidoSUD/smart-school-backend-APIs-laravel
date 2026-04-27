<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamGroupStudent;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Exam.php
 */
class ExamController extends Controller
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


        
        $examResult = ExamSchedule::where('session_id', $studentSession->session_id)->get();
        
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
        $feecategoryId = $request->get('feecategory_id');
        
        $data = Exam::where('sesion_id', $feecategoryId)->get();
        
        return $this->successResponse($data);
        }



    public function getStudentCategoryFee(Request $request): JsonResponse
    {
        $type = $request->post('type');
        $classId = $request->post('class_id');
        
        $data = Exam::where('sesion_id', $type)
            ->where('class_id', $classId)
            ->get();
        
        $status = $data->isEmpty() ? 'fail' : 'success';
        
        return $this->successResponse($data, null, $status);
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
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $examResult = ExamGroupStudent::where('student_session_id', $studentSession->id)
            ->with('examGroup')
            ->get();
        
        $data = [
            'exam_result' => $examResult,
        ];
        
        return $this->successResponse($data);
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
