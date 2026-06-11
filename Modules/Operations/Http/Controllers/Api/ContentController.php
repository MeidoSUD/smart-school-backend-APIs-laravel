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

    public function list(): JsonResponse
    {
        return $this->successResponse(['title' => 'Downloads']);
        }



    public function getsharelist(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);
        
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
        $content = Content::find($id);
        
        if (!$content) {
            return $this->errorResponse('Content not found', null, 404);
            }


        
        $data = [
            'title' => 'Upload Content',
            'content' => $content,
            'superadmin_restriction' => true,
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
        $studentSession = $this->studentSession($request);
        
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
        $studentSession = $this->studentSession($request);
        
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



}
