<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    protected $table = 'syllabus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'class_section_id', 'subject_id', 'topic', 'sub_topic', 'lesson_number',
        'teaching_method', 'notes', 'attachment', 'lacture_video', 'is_active',
    ];
}

class SyllabusStatus extends Model
{
    protected $table = 'syllabus_status';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'topic_id', 'status', 'complete_date'];
}

class SyllabusMessage extends Model
{
    protected $table = 'syllabus_message';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'type', 'student_id', 'message', 'created_date'];
}

class SyllabusReport extends Model
{
    protected $table = 'syllabus_report';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'lesson_plan_id', 'status', 'complete_date'];
}

class LessonPlan extends Model
{
    protected $table = 'lesson_plan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'class_section_id', 'subject_id', 'date', 'time_from', 'time_to',
        'topic', 'sub_topic', 'teaching_method', 'attachment', 'lacture_video', 'note', 'created_by',
    ];
}

class LessonPlanTopic extends Model
{
    protected $table = 'lesson_plan_topic';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['lesson_plan_id', 'name', 'status', 'complete_date'];
}