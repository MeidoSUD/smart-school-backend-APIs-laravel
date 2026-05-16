<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectGroupClassSection extends Model
{
    protected $table = 'subject_group_class_sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_group_id', 'session_id', 'is_active'];
}
