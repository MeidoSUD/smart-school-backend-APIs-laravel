<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    protected $table = 'syllabus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'class_section_id', 'subject_id', 'topic', 'sub_topic', 'lesson_number',
        'teaching_method', 'notes', 'attachment', 'lacture_video', 'is_active',
    ];
}
