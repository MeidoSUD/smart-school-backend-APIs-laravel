<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupClassBatchExam extends Model
{
    protected $table = 'exam_group_class_batch_exams';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam', 'passing_percentage', 'session_id', 'date_from', 'date_to',
        'exam_group_id', 'use_exam_roll_no', 'is_publish', 'is_rank_generated',
        'description', 'is_active',
    ];
}
