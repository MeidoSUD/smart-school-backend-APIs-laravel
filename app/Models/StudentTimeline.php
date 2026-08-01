<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTimeline extends Model
{
    protected $table = 'student_timeline';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'title', 'timeline_date', 'description', 'document',
        'status', 'date', 'created_student_id',
    ];

    protected $casts = [];
}
