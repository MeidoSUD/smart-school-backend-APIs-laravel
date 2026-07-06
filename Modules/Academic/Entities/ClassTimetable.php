<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Staff\Entities\Staff;

class ClassTimetable extends Model
{
    protected $table = 'subject_timetable';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'class_id', 'section_id', 'subject_group_id', 'subject_group_subject_id', 'staff_id', 'day', 'time_from', 'time_to', 'start_time', 'end_time', 'room_no', 'is_active'];

    public function subjectGroupSubject(): BelongsTo
    {
        return $this->belongsTo(SubjectGroupSubject::class, 'subject_group_subject_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
