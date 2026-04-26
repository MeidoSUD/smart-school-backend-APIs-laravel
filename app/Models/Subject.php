<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGroup extends Model
{
    protected $table = 'subject_group';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['group_name', 'is_active'];
}

class SubjectGroupSubject extends Model
{
    protected $table = 'subject_groupSubjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['subject_group_id', 'subject_id', 'session_id'];
}

class SubjectGroupClassSection extends Model
{
    protected $table = 'subject_group_class_sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_group_id', 'session_id', 'is_active'];
}

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'type', 'code', 'teacher_id', 'is_active'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }
}

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['teacher_id', 'subject_id', 'class_section_id', 'session_id'];
}

class ClassTimetable extends Model
{
    protected $table = 'class_timetable';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_id', 'staff_id', 'day', 'time_from', 'time_to', 'room_no', 'session_id'];
}