<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HostelRoom;
use Illuminate\Http\JsonResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Hostelroom.php
 */
class HostelRoomController extends Controller
{
    public function index(): JsonResponse
    {
        $listroom = HostelRoom::where('is_active', 'yes')->get();
        
        $data = [
            'listroom' => $listroom,
        ];
        
        return $this->successResponse($data);
    }
}