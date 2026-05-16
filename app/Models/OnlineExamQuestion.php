<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExamQuestion extends Model
{
    protected $table = 'online_exam_questions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'question_id', 'optional'];
}
