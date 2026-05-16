<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['teacher_id', 'subject_id', 'class_section_id', 'session_id'];
}
