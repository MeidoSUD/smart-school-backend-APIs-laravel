<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Models\ReadNotification;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Setting;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends \App\Http\Controllers\Api\Controller
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'publish_date' => 'required|date',
            'attachment' => 'nullable|string|max:255',
            'visible_student' => 'nullable|boolean',
            'visible_staff' => 'nullable|boolean',
            'visible_parent' => 'nullable|boolean',
        ]);

        $validated['visible_student'] = $validated['visible_student'] ?? false;
        $validated['visible_staff'] = $validated['visible_staff'] ?? false;
        $validated['visible_parent'] = $validated['visible_parent'] ?? false;
        $validated['is_active'] = 'yes';
        $validated['date'] = $validated['publish_date'];

        $user = $request->user();
        $validated['created_by'] = $user->id;
        $validated['created_id'] = $user->id;

        $notification = Notification::create($validated);

        return $this->successResponse(['notification' => $notification], 'Notification created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        if ($notification->created_id) {
            $staff = Staff::find($notification->created_id);
            $setting = Setting::first();
            $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;

            if ($staff && (!$superadminRestriction || $staff->role_id != 7)) {
                $notification->created_by = ($staff->surname ? $staff->name . ' ' . $staff->surname : $staff->name) . ' (' . $staff->employee_id . ')';
            } else {
                $notification->created_by = '';
            }
        }

        return $this->successResponse(['notificationlist' => $notification]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
            'publish_date' => 'sometimes|required|date',
            'attachment' => 'nullable|string|max:255',
            'visible_student' => 'nullable|boolean',
            'visible_staff' => 'nullable|boolean',
            'visible_parent' => 'nullable|boolean',
            'is_active' => 'nullable|string|in:yes,no',
        ]);

        if (isset($validated['publish_date'])) {
            $validated['date'] = $validated['publish_date'];
        }

        $notification->update($validated);

        return $this->successResponse(['notification' => $notification], 'Notification updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted successfully');
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

    public function download($id, Request $request): JsonResponse
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
