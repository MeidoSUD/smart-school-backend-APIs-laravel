<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupClassBatchExamStudent extends Model
{
    protected $table = 'exam_group_class_batch_exam_students';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_group_class_batch_exam_id', 'student_id', 'student_session_id',
        'roll_no', 'teacher_remark', 'rank', 'is_active',
    ];
}
