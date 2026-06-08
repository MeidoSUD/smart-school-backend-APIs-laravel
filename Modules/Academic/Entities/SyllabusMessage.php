<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class SyllabusMessage extends Model
{
    protected $table = 'lesson_plan_forum';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'subject_syllabus_id',
        'type',
        'staff_id',
        'student_id',
        'message',
        'created_date',
    ];
}
