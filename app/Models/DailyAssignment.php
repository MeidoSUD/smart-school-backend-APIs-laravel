<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAssignment extends Model
{
    protected $table = 'daily_assignment';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'subject_group_subject_id', 'title',
        'description', 'attachment', 'date', 'evaluated_by', 'evaluation_date', 'remark',
    ];
}
