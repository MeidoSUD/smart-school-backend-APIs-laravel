<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

// FIXME: Table 'syllabus_report' does not exist in SQL schema
class SyllabusReport extends Model
{
    protected $table = 'syllabus_report';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'lesson_plan_id', 'status', 'complete_date'];
}
