<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSubjectAttendance extends Model
{
    protected $table = 'student_subject_attendances';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'subject_id', 'class_section_id', 'date',
        'attendence_type', 'remarks', 'created_by',
    ];
}
