<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'event_title', 'event_description', 'start_date', 'end_date',
        'event_type', 'event_color', 'event_for', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
