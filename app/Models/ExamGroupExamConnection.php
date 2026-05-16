<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroupExamConnection extends Model
{
    protected $table = 'exam_group_exam_connections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_group_id', 'exam_group_class_batch_exams_id', 'exam_weightage', 'is_active'];
}
