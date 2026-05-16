<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'notice_date', 'publish_date', 'message', 'visible_at',
        'class_id', 'section_id', 'created_by', 'created_for', 'is_active', 'attachment',
    ];
}