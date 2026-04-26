<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'sesion_id', 'note', 'is_active'];
}

class ExamSchedule extends Model
{
    protected $table = 'exam_schedules';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'session_id', 'exam_id', 'teacher_subject_id', 'date_of_exam',
        'start_to', 'end_from', 'room_no', 'full_marks', 'passing_marks', 'note', 'is_active',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}

class ExamResult extends Model
{
    protected $table = 'exam_results';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_schedule_id', 'student_id', 'student_session_id',
        'attendence', 'mark_obtained', 'note', 'is_active',
    ];
}

class ExamGroup extends Model
{
    protected $table = 'exam_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'exam_type', 'description', 'is_active'];
}

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

class ExamGroupStudent extends Model
{
    protected $table = 'exam_group_students';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_group_id', 'student_id', 'student_session_id', 'is_active'];
}

class ExamGroupExamConnection extends Model
{
    protected $table = 'exam_group_exam_connections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_group_id', 'exam_group_class_batch_exams_id', 'exam_weightage', 'is_active'];
}

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