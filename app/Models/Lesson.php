<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lesson';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'class_section_id', 'subject_id', 'date', 'time_from', 'time_to',
        'topic', 'sub_topic', 'teaching_method', 'attachment', 'lacture_video', 'note', 'created_by',
    ];
}
