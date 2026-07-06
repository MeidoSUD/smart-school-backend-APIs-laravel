<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class LessonPlanTopic extends Model
{
    protected $table = 'topic';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'lesson_id', 'name', 'status', 'complete_date'];
}
