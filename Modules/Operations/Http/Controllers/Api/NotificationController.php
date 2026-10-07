<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Notification;
use Modules\Operations\Entities\ReadNotification;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Staff\Entities\Staff;
use Modules\Core\Entities\Setting;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Notification.php
 * Source of truth: codeigniter/application/controllers/user/Notification.php
 * + api/user/Notification.php + Notification_model + views/user/notification/*
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

        if (!$user || !in_array($user->role, ['student', 'parent'], true)) {
            return $this->errorResponse('Notifications not available for this role');
        }

        // CI parity: Notification_model::getNotificationForStudent($students.id)
        // joins staff + read_notification, filters visible_student/visible_parent='Yes',
        // orders publish_date desc, then controller filters publish_date <= today.
        // MySQL collation is case-insensitive so 'yes' matches CI 'Yes'; DB stores 'yes'.
        $studentId = $user->role === 'student' ? $user->user_id : null;
        $visibleColumn = $user->role === 'student' ? 'visible_student' : 'visible_parent';

        $notificationList = DB::table('send_notification')
            ->leftJoin('staff', 'staff.id', '=', 'send_notification.created_id')
            ->leftJoin('read_notification', function ($join) use ($user, $studentId) {
                $join->on('read_notification.notification_id', '=', 'send_notification.id');
                if ($user->role === 'student') {
                    $join->where('read_notification.student_id', '=', $studentId);
                } elseif ($user->role === 'parent') {
                    $join->where('read_notification.parent_id', '=', $user->id);
                }
            })
            ->where('send_notification.is_active', 'yes')
            ->where('send_notification.'.$visibleColumn, 'yes')
            ->orderByDesc('send_notification.publish_date')
            ->select(
                'send_notification.id',
                'send_notification.title',
                'send_notification.publish_date',
                'send_notification.date',
                'send_notification.message',
                'send_notification.attachment',
                'staff.employee_id',
                'staff.name',
                'staff.surname',
                DB::raw("IF(read_notification.id IS NULL, 'unread', 'read') as notification_id")
            )
            ->get()
            ->filter(fn ($notification) => strtotime(date('Y-m-d')) >= strtotime($notification->publish_date))
            ->values();

        return $this->successResponse([
            'title' => 'Notifications',
            'notificationlist' => $notificationList,
        ]);
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

            return $this->successResponse(['notification' => true], 'Notification marked as read');
        }

        return $this->errorResponse('Invalid notification ID');
    }

    public function download($id, Request $request): JsonResponse|BinaryFileResponse
    {
        $user = $request->user();

        $notification = Notification::where('id', $id)
            ->where('is_active', 'yes')
            ->where('publish_date', '<=', now()->toDateString())
            ->where(function ($query) use ($user) {
                if ($user->role === 'student') {
                    $query->where('visible_student', 'yes');
                } elseif ($user->role === 'parent') {
                    $query->where('visible_parent', 'yes');
                }
            })
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        return $this->sendStoredFile($notification->attachment, 'uploads/notice_board_images');
    }

    public function notification(Request $request, $id = null): JsonResponse
    {
        // CI parity: user/notification/notification POST {message_id}.
        // Canonical Flutter route is GET /notification/{id}; accept both.
        $messageId = $id ?? $request->message_id ?? $request->id ?? $request->notice;
        $user = $request->user();

        if (!$messageId) {
            return $this->errorResponse('Invalid notification ID');
        }

        $row = DB::table('send_notification')
            ->leftJoin('staff', 'staff.id', '=', 'send_notification.created_id')
            ->leftJoin('read_notification', function ($join) use ($user) {
                $join->on('read_notification.notification_id', '=', 'send_notification.id');
                if ($user && $user->role === 'student') {
                    $join->where('read_notification.student_id', '=', $user->user_id);
                } elseif ($user && $user->role === 'parent') {
                    $join->where('read_notification.parent_id', '=', $user->id);
                }
            })
            ->where('send_notification.id', $messageId)
            ->select(
                'send_notification.*',
                'staff.name',
                'staff.surname',
                'staff.employee_id',
                'staff.id as staff_id',
                DB::raw("IF(read_notification.id IS NULL, 'unread', 'read') as notification_id")
            )
            ->first();

        if (!$row) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        // Enforce visibility per role (CI web lists only own role; keep secure).
        if ($user) {
            if ($user->role === 'student' && strtolower($row->visible_student ?? '') !== 'yes') {
                return $this->errorResponse('Notification not found', null, 404);
            }
            if ($user->role === 'parent' && strtolower($row->visible_parent ?? '') !== 'yes') {
                return $this->errorResponse('Notification not found', null, 404);
            }
        }

        $notificationlist = (array) $row;

        $setting = Setting::first();
        // CI source: api/user/Notification.php notification(): when
        // superadmin_restriction == 'disabled' hide creator only for role_id 7,
        // otherwise always show creator.
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? 'enabled') : 'enabled';

        $createdBy = '';
        if (!empty($notificationlist['created_id'])) {
            $staff = Staff::find($notificationlist['created_id']);
            if ($staff && !($superadminRestriction === 'disabled' && (int) ($staff->role_id ?? 0) === 7)) {
                $createdBy = (($staff->surname !== '' && $staff->surname !== null)
                    ? $staff->name.' '.$staff->surname.'  ('.$staff->employee_id.')'
                    : $staff->name.' ('.$staff->employee_id.')');
            }
        }
        $notificationlist['created_by'] = $createdBy;

        return $this->successResponse(['notificationlist' => $notificationlist]);
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
