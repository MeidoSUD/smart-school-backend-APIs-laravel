<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentIncidentComment extends Model
{
    protected $table = 'student_incident_comments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_incident_id',
        'comment',
        'type',
        'staff_id',
        'student_id',
        'created_date',
    ];
}