<?php

namespace App\Http\Controllers\Api;

 
use App\Models\VideoTutorial;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Video_tutorial.php
 */
class VideoTutorialController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('VideoTutorialController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $student = Student::find($studentSession->student_id);
        
        $videoList = VideoTutorial::whereHas('classSections', function ($q) use ($studentSession) {
            $q->where('class_section_id', $studentSession->id);
        })->get();
        
        $data = [
            'student' => $student,
            'video_list' => $videoList,
        ];
        
        return $this->successResponse($data);
        }



    public function view($id): JsonResponse
    {
        $video = VideoTutorial::find($id);
        
        if (!$video) {
            return $this->errorResponse('Video not found', null, 404);
            }


        
        return $this->successResponse(['video' => $video]);
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
