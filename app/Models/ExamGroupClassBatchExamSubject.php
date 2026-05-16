<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupClassBatchExamSubject extends Model
{
    protected $table = 'exam_group_class_batch_exam_subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_group_class_batch_exams_id', 'subject_id', 'date_from', 'time_from',
        'duration', 'room_no', 'max_marks', 'min_marks', 'credit_hours', 'date_to', 'is_active',
    ];
}
