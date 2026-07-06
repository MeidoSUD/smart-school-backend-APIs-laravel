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

        $studentSession = StudentSession::where('student_id', $studentId)->first();

        if (!$studentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $data = [
            'vehroute_id' => $studentSession->vehroute_id,
            'route_pickup_point_id' => $studentSession->route_pickup_point_id,
        ];

        return $this->successResponse(['listroute' => $data]);
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
