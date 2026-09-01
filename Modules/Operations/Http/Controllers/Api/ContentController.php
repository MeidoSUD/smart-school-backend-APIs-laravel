<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Content;
use Modules\Operations\Entities\ShareContent;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Content.php
 */
class ContentController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ContentController');
        }

    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $role = $user->role;
        $setting = Setting::where('is_active', 'yes')->first();
        $superadmin_restriction = $setting->superadmin_restriction ?? 'enabled';

        if ($role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
            $parentUserId = $user->id;
        } else {
            $studentId = $user->user_id;
            $parentUserId = null;
        }

        $classSectionId = \DB::table('class_sections')
            ->where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->value('id');

        $query = \DB::table('share_contents')
            ->join('staff', 'share_contents.created_by', '=', 'staff.id')
            ->join('staff_roles', 'staff_roles.staff_id', '=', 'staff.id')
            ->whereIn('share_contents.id', function ($q) use ($role, $studentId, $parentUserId, $classSectionId) {
                $q->select('share_content_id')
                    ->from('share_content_for')
                    ->where(function ($q2) use ($role, $studentId, $parentUserId, $classSectionId) {
                        if ($role === 'student') {
                            $q2->where('group_id', 'student')
                                ->orWhere('student_id', $studentId)
                                ->orWhere('class_section_id', $classSectionId);
                        } elseif ($role === 'parent') {
                            $q2->where('group_id', 'parent')
                                ->orWhere('user_parent_id', $parentUserId)
                                ->orWhere('class_section_id', $classSectionId);
                        }
                    });
            })
            ->select(
                'share_contents.id',
                'share_contents.title',
                'share_contents.send_to',
                'share_contents.share_date',
                'share_contents.valid_upto',
                'share_contents.description',
                'share_contents.created_by',
                'staff.name',
                'staff.surname',
                'staff.employee_id',
                'staff_roles.role_id'
            )
            ->orderBy('share_contents.id', 'desc')
            ->get();

        $contents = $query->map(function ($item) use ($superadmin_restriction) {
            $sharedBy = '';
            if (!($superadmin_restriction === 'disabled' && $item->role_id == 7)) {
                $sharedBy = trim($item->name . ' ' . $item->surname) . ' (' . $item->employee_id . ')';
            }

            return [
                'id' => $item->id,
                'title' => $item->title,
                'send_to' => $item->send_to,
                'share_date' => $item->share_date,
                'valid_upto' => $item->valid_upto,
                'description' => $item->description,
                'shared_by' => $sharedBy,
                'staff_name' => $item->name,
                'staff_surname' => $item->surname,
                'employee_id' => $item->employee_id,
                'role_id' => $item->role_id,
            ];
        });

        return $this->successResponse([
            'title' => 'Downloads',
            'contents' => $contents,
        ]);
    }



    public function getsharelist(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $role = $user->role;
        
        $contents = Content::where('is_active', 'yes')
            ->where(function ($query) use ($studentSession, $role) {
                $query->where('is_public', 'Yes')
                    ->orWhere(function ($q) use ($studentSession, $role) {
                        $q->where('class_id', $studentSession->class_id)
                            ->where('cls_sec_id', $studentSession->section_id);
                    });
            })
            ->get();
        
        return $this->successResponse(['contents' => $contents]);
        }



    public function view($id): JsonResponse
    {
        $content = \DB::table('share_contents')
            ->join('staff', 'staff.id', '=', 'share_contents.created_by')
            ->join('staff_roles', 'staff_roles.staff_id', '=', 'staff.id')
            ->where('share_contents.id', $id)
            ->select(
                'share_contents.*',
                'staff.name',
                'staff.surname',
                'staff.employee_id',
                'staff_roles.role_id'
            )
            ->first();

        if (!$content) {
            return $this->errorResponse('Content not found', null, 404);
        }

        $content->upload_contents = \DB::table('share_upload_contents')
            ->join('upload_contents', 'upload_contents.id', '=', 'share_upload_contents.upload_content_id')
            ->where('share_upload_contents.share_content_id', $id)
            ->select(
                'share_upload_contents.*',
                'upload_contents.real_name',
                'upload_contents.thumb_path',
                'upload_contents.dir_path',
                'upload_contents.img_name',
                'upload_contents.thumb_name',
                'upload_contents.file_type',
                'upload_contents.mime_type',
                'upload_contents.vid_url',
                'upload_contents.vid_title'
            )
            ->get();

        $setting = Setting::where('is_active', 'yes')->first();
        $superadmin_restriction = $setting->superadmin_restriction ?? 'enabled';

        $today = now()->toDateString();
        $isValid = true;
        if (isset($content->share_date) && $content->share_date > $today) {
            $isValid = false;
        }
        if (!empty($content->valid_upto) && $content->valid_upto < $today) {
            $isValid = false;
        }

        $data = [
            'title' => 'Upload Content',
            'content' => $content,
            'superadmin_restriction' => $superadmin_restriction,
            'is_valid' => $isValid,
        ];

        return $this->successResponse($data);
    }



    public function index(): JsonResponse
    {
        $list = Content::where('is_active', 'yes')->get();
        
        $data = [
            'title' => 'Upload Content',
            'title_list' => 'Upload Content List',
            'list' => $list,
        ];
        
        return $this->successResponse($data);
        }



    public function assignment(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $list = Content::where('is_active', 'yes')
            ->where('type', 'assignments')
            ->where('class_id', $studentSession->class_id)
            ->where('cls_sec_id', $studentSession->section_id)
            ->get();
        
        $data = [
            'title_list' => 'List of Assignment',
            'list' => $list,
        ];
        
        return $this->successResponse($data);
        }



    public function studymaterial(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $list = Content::where('is_active', 'yes')
            ->where('type', 'study_material')
            ->where('class_id', $studentSession->class_id)
            ->where('cls_sec_id', $studentSession->section_id)
            ->get();
        
        $data = [
            'title_list' => 'List of Study Material',
            'list' => $list,
        ];
        
        return $this->successResponse($data);
        }



    public function download($file): JsonResponse
    {
        return $this->successResponse(['file' => $file]);
    }

    public function syllabus(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $list = Content::where('is_active', 'yes')
            ->where('type', 'syllabus')
            ->where('class_id', $studentSession->class_id)
            ->where('cls_sec_id', $studentSession->section_id)
            ->get();

        $data = [
            'title_list' => 'List of Syllabus',
            'list' => $list,
        ];

        return $this->successResponse($data);
    }

    public function other(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $list = Content::where('is_active', 'yes')
            ->where('type', 'other_download')
            ->where('class_id', $studentSession->class_id)
            ->where('cls_sec_id', $studentSession->section_id)
            ->get();

        $data = [
            'title_list' => 'List of Other Download',
            'list' => $list,
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


        
        $setting = Setting::where('is_active', 'yes')->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->session_id))
            ->first();
        }


    }
