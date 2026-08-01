<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectSyllabus extends Model
{
    protected $table = 'subject_syllabus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'subject_id', 'class_section_id', 'syllabus_title', 'description',
        'date_from', 'date_to', 'created_by', 'is_active',
    ];
}
