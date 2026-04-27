<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Hostel;
use Illuminate\Http\JsonResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Hostel.php
 */
class HostelController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('HostelController');
        }

    public function index(): JsonResponse
    {
        $listhostel = Hostel::where('is_active', 'yes')->get();
        
        $data = [
            'listhostel' => $listhostel,
        ];
        
        return $this->successResponse($data);
        }


    }
