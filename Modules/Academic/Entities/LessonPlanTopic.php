<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class LessonPlanTopic extends Model
{
    protected $table = 'lesson_plan_topic';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['lesson_plan_id', 'name', 'status', 'complete_date'];
}
