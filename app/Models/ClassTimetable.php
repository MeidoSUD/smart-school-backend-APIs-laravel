<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTimetable extends Model
{
    protected $table = 'class_timetable';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_id', 'staff_id', 'day', 'time_from', 'time_to', 'room_no', 'session_id'];
}
