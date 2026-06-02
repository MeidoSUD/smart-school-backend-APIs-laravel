<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class SyllabusMessage extends Model
{
    protected $table = 'syllabus_message';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['syllabus_id', 'type', 'student_id', 'message', 'created_date'];
}
