<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\VideoTutorial;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $student = Student::find($studentSession->student_id);

        // CI: Video_tutorial_model::getvideotutorial() filters by the
        // student's own class_id + section_id via class_sections.
        $videos = $this->scopedVideos($studentSession)->get();

        $setting = Setting::where('is_active', 'yes')->first();

        $data = [
            'student' => $student,
            'video_list' => $this->withStaffNames($videos, $setting),
        ];

        return $this->successResponse($data);
        }



    public function view(Request $request, $id): JsonResponse
    {
        // Same class/section scope as index: a student can only open videos
        // of their own class (CI never exposes other classes' videos).
        $studentSession = $this->getStudentSession($request->user());

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }

        $video = $this->scopedVideos($studentSession)->where('video_tutorial.id', $id)->first();

        if (!$video) {
            return $this->errorResponse('Video not found', null, 404);
            }

        $setting = Setting::where('is_active', 'yes')->first();
        $row = $this->withStaffNames(collect([$video]), $setting)->first();

        return $this->successResponse(['video' => $row]);
        }

    /**
     * CI-equivalent scope: videos linked (video_tutorial_class_sections)
     * to the student's class_id + section_id, newest first.
     */
    private function scopedVideos($studentSession)
    {
        return VideoTutorial::whereHas('classSections', function ($q) use ($studentSession) {
            $q->where('class_sections.class_id', $studentSession->class_id)
                ->where('class_sections.section_id', $studentSession->section_id);
        })->orderBy('video_tutorial.id', 'DESC');
    }

    /**
     * CI genratediv(): modal shows the creator name (+ employee id), hidden
     * for superadmin (role 7) when sch_settings.superadmin_restriction is
     * 'disabled'.
     */
    private function withStaffNames($videos, $setting)
    {
        $restriction = $setting ? $setting->getAttribute('superadmin_restriction') : null;
        $creatorIds = $videos->pluck('created_by')->filter()->unique()->values();

        $staff = $creatorIds->isEmpty()
            ? collect()
            : DB::table('staff')->whereIn('id', $creatorIds)->get()->keyBy('id');
        $roles = $creatorIds->isEmpty()
            ? collect()
            : DB::table('staff_roles')->whereIn('staff_id', $creatorIds)->pluck('role_id', 'staff_id');

        return $videos->map(function ($video) use ($staff, $roles, $restriction) {
            $row = $video->toArray();
            $name = '';
            if ($video->created_by && isset($staff[$video->created_by])) {
                $member = $staff[$video->created_by];
                $roleId = $roles[$video->created_by] ?? null;
                if (!($restriction === 'disabled' && (int) $roleId === 7)) {
                    $name = trim(($member->name ?? '') . ' ' . ($member->surname ?? ''));
                    if (!empty($member->employee_id)) {
                        $name .= ' (' . $member->employee_id . ')';
                    }
                }
            }
            $row['staff_name'] = $name;
            return $row;
        })->values();
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
