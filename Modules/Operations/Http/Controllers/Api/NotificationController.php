<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Notification;
use Modules\Operations\Entities\ReadNotification;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\Staff;
use Modules\Core\Entities\Setting;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Notification.php
 */
class NotificationController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('NotificationController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notification::where('is_active', 'yes')
            ->where('publish_date', '<=', now()->toDateString())
            ->orderByDesc('publish_date');

        if ($user->role === 'student') {
            $query->where('visible_student', 'yes');
        } elseif ($user->role === 'parent') {
            $query->where('visible_parent', 'yes');
        } else {
            return $this->errorResponse('Notifications not available for this role');
        }

        $notificationList = $query->get()
            ->filter(fn ($notification) => strtotime(date('Y-m-d')) >= strtotime($notification->publish_date))
            ->values();

        return $this->successResponse(['notificationlist' => $notificationList]);
    }



    #[BodyParameter('notification_id', description: 'Notification ID to mark as read', type: 'integer', required: true, example: 1)]
    public function updatestatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $notificationId = $request->notification_id;

        $result = ReadNotification::markAsRead((int) $notificationId, $user);

        if (!$result) {
            return $this->errorResponse('Unable to update notification status');
        }

        return $this->successResponse(['notification' => true], 'Status updated successfully');
        }



    public function read(Request $request): JsonResponse
    {
        $notificationId = $request->notice;
        $user = $request->user();

        if ($notificationId) {
            $result = ReadNotification::markAsRead((int) $notificationId, $user);

            if (!$result) {
                return $this->errorResponse('Unable to mark notification as read');
            }

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
        
        if ($notificationlist->created_id) {
            $staff = Staff::find($notificationlist->created_id);
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
