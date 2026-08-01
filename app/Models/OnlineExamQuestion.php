<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExamQuestion extends Model
{
    protected $table = 'onlineexam_questions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['onlineexam_id', 'question_id', 'marks', 'is_active'];

    public function onlineExam()
    {
        return $this->belongsTo(OnlineExam::class, 'onlineexam_id');
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class, 'question_id');
    }
}
