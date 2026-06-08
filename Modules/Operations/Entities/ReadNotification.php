<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\User;

class ReadNotification extends Model
{
    protected $table = 'read_notification';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'notification_id',
        'student_id',
        'parent_id',
        'staff_id',
        'is_active',
    ];

    public static function markAsRead(int $notificationId, User $user): bool
    {
        $lookup = ['notification_id' => $notificationId];
        $data = ['notification_id' => $notificationId, 'is_active' => 'yes'];

        if ($user->role === 'student') {
            $lookup['student_id'] = $user->user_id;
            $data['student_id'] = $user->user_id;
        } elseif ($user->role === 'parent') {
            $lookup['parent_id'] = $user->id;
            $data['parent_id'] = $user->id;
        } else {
            return false;
        }

        if (static::where($lookup)->exists()) {
            return true;
        }

        static::create($data);

        return true;
    }
}
