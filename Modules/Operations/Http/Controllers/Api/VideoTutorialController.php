<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\VideoTutorial;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Video_tutorial.php
 */
class VideoTutorialController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('VideoTutorialController');
        }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);
        
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



}
