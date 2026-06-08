<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\HostelRoom;
use Illuminate\Http\JsonResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Hostelroom.php
 */
class HostelRoomController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('HostelRoomController');
        }

    public function index(): JsonResponse
    {
        $listroom = HostelRoom::all();
        
        $data = [
            'listroom' => $listroom,
        ];
        
        return $this->successResponse($data);
        }


    }
