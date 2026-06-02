<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class HomeworkEvaluation extends Model
{
    protected $table = 'homework_evaluation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['homework_id', 'student_id', 'docs', 'evaluation_date', 'remark', 'created_by'];
}
