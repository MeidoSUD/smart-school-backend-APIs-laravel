<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
