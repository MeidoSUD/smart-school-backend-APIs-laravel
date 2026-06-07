<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\TransportRoute;
use Modules\Operations\Entities\PickupPoint;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Route.php
 */
class RouteController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('RouteController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        
        $studentList = Student::find($studentId);
        
        if (!$studentList) {
            return $this->errorResponse('Student not found', null, 404);
            }


        
        $pickupPoint = [];
        if ($studentList->route_id) {
            $pickupPoint = PickupPoint::where('route_id', $studentList->route_id)->get();
            }


        
        $studentList->pickup_point = $pickupPoint;
        
        $data = ['listroute' => $studentList];
        
        return $this->successResponse($data);
        }



    #[BodyParameter('vehrouteid', description: 'Vehicle route ID', type: 'integer', required: true, example: 1)]
    public function getbusdetail(Request $request): JsonResponse
    {
        $vehrouteid = $request->post('vehrouteid');
        
        $result = [];
        
        return $this->successResponse($result);
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
