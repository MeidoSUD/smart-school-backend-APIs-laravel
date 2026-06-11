<?php

namespace Modules\Operations\Http\Controllers\Api;

use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Operations\Services\NoticeNotificationService;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Notification.php
 */
class NotificationController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private NoticeNotificationService $noticeNotifications,
    ) {
        $this->setControllerName('NotificationController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['student', 'parent'], true)) {
            return $this->errorResponse('Notifications not available for this role');
        }

        return $this->successResponse([
            'notificationlist' => $this->noticeNotifications->listForUser($user),
        ]);
    }

    #[BodyParameter('notification_id', description: 'Notification ID to mark as read', type: 'integer', required: true, example: 1)]
    public function updatestatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $notificationId = (int) $request->notification_id;

        if (!$this->noticeNotifications->markAsRead($user, $notificationId)) {
            return $this->errorResponse('Unable to update notification status');
        }

        return $this->successResponse(['notification' => true], 'Status updated successfully');
    }

    public function read(Request $request): JsonResponse
    {
        $notificationId = $request->notice;
        $user = $request->user();

        if (!$notificationId) {
            return $this->errorResponse('Invalid notification ID');
        }

        if (!$this->noticeNotifications->markAsRead($user, (int) $notificationId)) {
            return $this->errorResponse('Unable to mark notification as read');
        }

        return $this->successResponse(null, 'Notification marked as read');
    }

    public function download($id): JsonResponse
    {
        $notification = $this->noticeNotifications->findNotice((int) $id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        return $this->successResponse(['attachment' => $notification->attachment]);
    }

    public function notification(Request $request): JsonResponse
    {
        $messageId = (int) $request->message_id;
        $notificationlist = $this->noticeNotifications->findNotice($messageId);

        if (!$notificationlist) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        $notificationlist = $this->noticeNotifications->enrichCreatedBy($notificationlist);

        return $this->successResponse(['notificationlist' => $notificationlist]);
    }
}
