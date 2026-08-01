<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExamResult extends Model
{
    protected $table = 'onlineexam_student_results';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'onlineexam_id', 'student_id', 'question_id', 'answer',
        'marks', 'is_correct', 'is_active',
    ];

    public function onlineExam()
    {
        return $this->belongsTo(OnlineExam::class, 'onlineexam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
