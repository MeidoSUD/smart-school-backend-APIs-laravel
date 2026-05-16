<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExam extends Model
{
    protected $table = 'online_exams';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'exam_title', 'exam_type', 'class_id', 'section_id', 'subject_id',
        'duration', 'minimum_percentage', 'max_attempts', 'is_active',
    ];
}
