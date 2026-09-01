<?php

namespace Modules\Academic\Entities;

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

    public function examGroupClassBatchExam()
    {
        return $this->belongsTo(ExamGroupClassBatchExam::class, 'exam_group_class_batch_exam_id');
    }
}
