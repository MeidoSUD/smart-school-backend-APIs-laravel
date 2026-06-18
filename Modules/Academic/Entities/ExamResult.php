<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $table = 'exam_results';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_schedule_id',
        'student_id',
        'attendence',
        'get_marks',
        'note',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
