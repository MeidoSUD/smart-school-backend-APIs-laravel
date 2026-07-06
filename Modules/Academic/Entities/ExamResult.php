<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    // FIXME: Table 'exam_results' does not exist in SQL schema
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


}
