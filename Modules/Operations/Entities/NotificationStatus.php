<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class NotificationStatus extends Model
{
    protected $table = 'notification_status';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['notification_id', 'user_id', 'visible_date_read'];
}
