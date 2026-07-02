<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Staff\Entities\Staff;

class ClassTimetable extends Model
{
    protected $table = 'class_timetable';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_id', 'staff_id', 'day', 'time_from', 'time_to', 'room_no', 'session_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
