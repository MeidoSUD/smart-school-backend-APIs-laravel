<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupStudent extends Model
{
    protected $table = 'exam_group_students';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_group_id', 'student_id', 'student_session_id', 'is_active'];
}
