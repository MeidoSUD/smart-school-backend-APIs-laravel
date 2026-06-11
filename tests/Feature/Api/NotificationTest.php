<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class NotificationTest extends ApiTestCase
{
    public function test_student_can_list_and_mark_notice_as_read(): void
    {
        $fixtures = $this->seedSchoolFixtures();
        $user = $fixtures['user'];

        $noticeId = DB::table('send_notification')->insertGetId([
            'title' => 'School Holiday',
            'publish_date' => now()->toDateString(),
            'date' => now()->toDateString(),
            'attachment' => '',
            'message' => 'School will be closed tomorrow.',
            'visible_student' => 'yes',
            'visible_staff' => 'no',
            'visible_parent' => 'no',
            'created_by' => 'Admin',
            'created_id' => null,
            'is_active' => 'yes',
            'created_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $listResponse = $this->getJson('/api/notification');

        $listResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['notificationlist']]);

        $notifications = $listResponse->json('data.notificationlist');
        $this->assertNotEmpty($notifications);
        $this->assertSame($noticeId, $notifications[0]['id']);
        $this->assertNull($notifications[0]['read_at']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => \Modules\Core\Entities\User::class,
            'notifiable_id' => $user->id,
            'type' => \Modules\Operations\Notifications\SchoolNoticeNotification::class,
        ]);

        $markResponse = $this->postJson('/api/notification/updatestatus', [
            'notification_id' => $noticeId,
        ]);

        $markResponse->assertOk()
            ->assertJsonPath('data.notification', true);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => \Modules\Core\Entities\User::class,
            'notifiable_id' => $user->id,
        ]);

        $readNotification = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('type', \Modules\Operations\Notifications\SchoolNoticeNotification::class)
            ->first();

        $this->assertNotNull($readNotification->read_at);
    }
}
