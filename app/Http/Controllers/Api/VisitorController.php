<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Visitor;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Visitors.php
 */
class VisitorController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('VisitorController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $visitorList = Visitor::where('student_session_id', $studentSession->id)
            ->orderBy('date', 'desc')
            ->get();
        
        $data = ['visitor_list' => $visitorList];
        
        return $this->successResponse($data);
        }



    public function download($id): JsonResponse
    {
        $visitorlist = Visitor::find($id);
        
        if (!$visitorlist) {
            return $this->errorResponse('Visitor not found', null, 404);
            }


        
        return $this->successResponse(['image' => $visitorlist->image]);
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
