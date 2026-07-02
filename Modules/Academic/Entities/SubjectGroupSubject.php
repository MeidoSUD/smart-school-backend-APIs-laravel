<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class SubjectGroupSubject extends Model
{
    protected $table = 'subject_group_subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['subject_group_id', 'subject_id', 'session_id'];
}
