<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Syllabus;
use App\Models\SyllabusStatus;
use App\Models\SyllabusMessage;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Syllabus.php
 */
class SyllabusController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('SyllabusController');
        }

    public function index(): JsonResponse
    {
        $startWeekday = Setting::first()->start_month ?? 4;
        $monday = Carbon::now()->startOfMonth()->startOfWeek();
        
        $data = [
            'this_week_start' => $monday->format('Y-m-d'),
            'this_week_end' => $monday->copy()->addDays(6)->format('Y-m-d'),
        ];
        
        return $this->successResponse($data);
        }



    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $subjects = Syllabus::where('class_section_id', $studentSession->class_id)->get();
        
        $subjectsData = [];
        foreach ($subjects as $value) {
            $complete = 0;
            $incomplete = 0;
            $total = 1;
            
            $statuses = SyllabusStatus::where('syllabus_id', $value->id)->get();
            if ($statuses->isNotEmpty()) {
                $total = $statuses->count();
                $complete = $statuses->where('status', 1)->count();
                $incomplete = $total - $complete;
                }


            
            $completePercent = $total > 0 ? round(($complete / $total) * 100) : 0;
            $incompletePercent = $total > 0 ? round(($incomplete / $total) * 100) : 0;
            
            $subjectsData[$value->id] = [
                'lebel' => $value->topic . ($value->code ? ' (' . $value->code . ')' : ''),
                'complete' => $completePercent,
                'incomplete' => $incompletePercent,
                'id' => $value->id,
                'total' => $total,
                'name' => $value->topic,
            ];
            }


        
        $data = [
            'subjects_data' => $subjectsData,
            'status' => ['1' => 'Complete', '0' => 'Incomplete'],
        ];
        
        return $this->successResponse($data);
        }



    public function download($id): JsonResponse
    {
        $result = Syllabus::find($id);
        
        if (!$result) {
            return $this->errorResponse('Syllabus not found', null, 404);
            }


        
        return $this->successResponse(['attachment' => $result->attachment]);
        }



    public function addmessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'syllabus_id' => 'required',
            'message' => 'required|string',
        ]);
        
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        
        SyllabusMessage::create([
            'syllabus_id' => $request->syllabus_id,
            'type' => 'student',
            'student_id' => $studentId,
            'message' => $request->message,
            'created_date' => now(),
        ]);
        
        return $this->successResponse(null, 'Message added successfully');
        }



    public function getmessage(Request $request): JsonResponse
    {
        $subjectSyllabusId = $request->syllabus_id;
        
        $messageList = SyllabusMessage::where('syllabus_id', $subjectSyllabusId)->get();
        
        $data = [
            'messagelist' => $messageList,
        ];
        
        return $this->successResponse($data);
        }



    private function getStudentSession($user)
    {
        $studentId = $this->getStudentId($user);
        
        if (!$studentId) {
            return null;
            }


        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
        }



    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
            }


        return null;
        }


    }
