<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'send_notification';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'publish_date',
        'date',
        'attachment',
        'message',
        'visible_student',
        'visible_staff',
        'visible_parent',
        'created_by',
        'created_id',
        'is_active',
    ];
}
