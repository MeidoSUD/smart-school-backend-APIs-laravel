<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
