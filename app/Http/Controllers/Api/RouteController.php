<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Route;
use App\Models\PickupPoint;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Route.php
 */
class RouteController extends Controller
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
