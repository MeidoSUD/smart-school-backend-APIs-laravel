<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Visitor;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Visitors.php
 */
class VisitorController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('VisitorController');
        }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);
        
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



}
