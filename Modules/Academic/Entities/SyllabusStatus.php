<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

// FIXME: Table 'syllabus_status' does not exist in SQL schema
class SyllabusStatus extends Model
{
    protected $table = 'syllabus_status';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'topic_id', 'status', 'complete_date'];
}
