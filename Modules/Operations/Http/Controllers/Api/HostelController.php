<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Hostel;
use Illuminate\Http\JsonResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Hostel.php
 */
class HostelController extends \Modules\Core\Http\Controllers\Api\Controller
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
