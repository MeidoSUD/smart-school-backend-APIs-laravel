<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkEvaluation extends Model
{
    protected $table = 'homework_evaluation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'homework_id',
        'student_id',
        'student_session_id',
        'marks',
        'note',
        'date',
        'status',
    ];
}
