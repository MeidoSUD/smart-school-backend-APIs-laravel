<?php

namespace Modules\Operations\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Operations\Entities\SchoolNotice;

class SchoolNoticeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SchoolNotice $notice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'notice_id' => $this->notice->id,
            'title' => $this->notice->title,
            'publish_date' => $this->notice->publish_date,
            'date' => $this->notice->date,
            'attachment' => $this->notice->attachment,
            'message' => $this->notice->message,
            'visible_student' => $this->notice->visible_student,
            'visible_staff' => $this->notice->visible_staff,
            'visible_parent' => $this->notice->visible_parent,
            'created_by' => $this->notice->created_by,
            'created_id' => $this->notice->created_id,
            'is_active' => $this->notice->is_active,
        ];
    }
}
