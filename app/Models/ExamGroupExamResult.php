<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupExamResult extends Model
{
    protected $table = 'exam_group_exam_results';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_group_class_batch_exam_student_id', 'exam_group_class_batch_exam_subject_id',
        'exam_group_student_id', 'attendence', 'get_marks', 'note', 'is_active',
    ];
}
