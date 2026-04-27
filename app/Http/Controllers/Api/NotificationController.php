<?php

namespace App\Http\Controllers\Api;

 
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Notification.php
 */
class NotificationController extends Controller
{
    public function __construct()
    {
        $this->setControllerName('NotificationController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role;
        
        $studentId = $this->getStudentId($user);
        
        $query = Notification::where('is_active', 'yes')
            ->where('publish_date', '<=', now());
        
        $notifications = $query->get();
        
        $notificationList = [];
        foreach ($notifications as $value) {
            if (strtotime(date('Y-m-d')) >= strtotime($value->publish_date)) {
                $notificationList[] = $value;
                }


            }


        
        $data = [
            'notificationlist' => $notificationList,
        ];
        
        return $this->successResponse($data);
        }



    public function updatestatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $notificationId = $request->notification_id;
        $studentId = $this->getStudentId($user);
        
        NotificationStatus::updateOrCreate(
            ['notification_id' => $notificationId, 'user_id' => $studentId],
            ['visible_date_read' => now()]
        );
        
        return $this->successResponse(['notification' => true], 'Status updated successfully');
        }



    public function read(Request $request): JsonResponse
    {
        $notificationId = $request->notice;
        $user = $request->user();
        $studentId = $this->getStudentId($user);
        
        if ($notificationId) {
            NotificationStatus::updateOrCreate(
                ['notification_id' => $notificationId, 'user_id' => $studentId],
                ['visible_date_read' => now()]
            );
            
            return $this->successResponse(null, 'Notification marked as read');
            }


        
        return $this->errorResponse('Invalid notification ID');
        }



    public function download($id): JsonResponse
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
            }


        
        return $this->successResponse(['attachment' => $notification->attachment]);
        }



    public function notification(Request $request): JsonResponse
    {
        $messageId = $request->message_id;
        
        $notificationlist = Notification::find($messageId);
        
        if (!$notificationlist) {
            return $this->errorResponse('Notification not found', null, 404);
            }


        
        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;
        
        if ($notificationlist->created_by) {
            $staff = Staff::find($notificationlist->created_by);
            if ($staff && (!$superadminRestriction || $staff->role_id != 7)) {
                $notificationlist->created_by = ($staff->surname ? $staff->name . ' ' . $staff->surname : $staff->name) . ' (' . $staff->employee_id . ')';
            } else {
                $notificationlist->created_by = '';
                }


            }


        
        $data = ['notificationlist' => $notificationlist];
        
        return $this->successResponse($data);
        }



    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            return $user->id;
            }


        return null;
        }


    }
