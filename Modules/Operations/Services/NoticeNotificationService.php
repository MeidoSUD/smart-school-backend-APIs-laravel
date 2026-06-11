<?php

namespace Modules\Operations\Services;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;
use Modules\Operations\Entities\ReadNotification;
use Modules\Operations\Entities\SchoolNotice;
use Modules\Operations\Notifications\SchoolNoticeNotification;
use Modules\Staff\Entities\Staff;

class NoticeNotificationService
{
    public function listForUser(User $user): array
    {
        if (!$this->supportsRole($user)) {
            return [];
        }

        if (!Schema::hasTable('send_notification')) {
            return [];
        }

        $this->syncForUser($user);

        return $user->notifications()
            ->where('type', SchoolNoticeNotification::class)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->toLegacyShape($notification))
            ->values()
            ->all();
    }

    public function markAsRead(User $user, int $noticeId): bool
    {
        if (!$this->supportsRole($user)) {
            return false;
        }

        $this->syncForUser($user);

        $notification = $this->findUserNotice($user, $noticeId);

        if (!$notification) {
            return false;
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return true;
    }

    public function findNotice(int $noticeId): ?SchoolNotice
    {
        if (!Schema::hasTable('send_notification')) {
            return null;
        }

        return SchoolNotice::find($noticeId);
    }

    public function enrichCreatedBy(SchoolNotice $notice): SchoolNotice
    {
        if (!$notice->created_id) {
            $notice->created_by = '';

            return $notice;
        }

        $staff = Staff::find($notice->created_id);

        if (!$staff) {
            $notice->created_by = '';

            return $notice;
        }

        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;

        if ($superadminRestriction && $staff->role_id == 7) {
            $notice->created_by = '';

            return $notice;
        }

        $name = $staff->surname
            ? $staff->name . ' ' . $staff->surname
            : $staff->name;

        $notice->created_by = $name . ' (' . $staff->employee_id . ')';

        return $notice;
    }

    private function syncForUser(User $user): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $notices = SchoolNotice::published()
            ->visibleTo($user)
            ->get()
            ->filter(fn (SchoolNotice $notice) => strtotime(date('Y-m-d')) >= strtotime($notice->publish_date));

        foreach ($notices as $notice) {
            $existing = $this->findUserNotice($user, (int) $notice->id);

            if ($existing) {
                continue;
            }

            $user->notify(new SchoolNoticeNotification($notice));

            if ($this->wasReadInLegacy($user, (int) $notice->id)) {
                $this->findUserNotice($user, (int) $notice->id)?->markAsRead();
            }
        }
    }

    private function findUserNotice(User $user, int $noticeId): ?DatabaseNotification
    {
        return $user->notifications()
            ->where('type', SchoolNoticeNotification::class)
            ->where('data->notice_id', $noticeId)
            ->first();
    }

    private function wasReadInLegacy(User $user, int $noticeId): bool
    {
        if (!Schema::hasTable('read_notification')) {
            return false;
        }

        $query = ReadNotification::where('notification_id', $noticeId)
            ->where('is_active', 'yes');

        if ($user->role === 'student') {
            return $query->where('student_id', $user->user_id)->exists();
        }

        if ($user->role === 'parent') {
            return $query->where('parent_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegacyShape(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $data['notice_id'] ?? null,
            'title' => $data['title'] ?? null,
            'publish_date' => $data['publish_date'] ?? null,
            'date' => $data['date'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'message' => $data['message'] ?? null,
            'visible_student' => $data['visible_student'] ?? null,
            'visible_staff' => $data['visible_staff'] ?? null,
            'visible_parent' => $data['visible_parent'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'created_id' => $data['created_id'] ?? null,
            'is_active' => $data['is_active'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
        ];
    }

    private function supportsRole(User $user): bool
    {
        return in_array($user->role, ['student', 'parent'], true);
    }
}
