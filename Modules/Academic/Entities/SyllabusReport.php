<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class SyllabusReport extends Model
{
    protected $table = 'syllabus_report';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'lesson_plan_id', 'status', 'complete_date'];
}
