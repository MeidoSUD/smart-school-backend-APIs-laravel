<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectGroupSubject extends Model
{
    protected $table = 'subject_groupSubjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['subject_group_id', 'subject_id', 'session_id'];
}
