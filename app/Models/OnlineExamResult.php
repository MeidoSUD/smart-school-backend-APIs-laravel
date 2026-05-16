<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineExamResult extends Model
{
    protected $table = 'online_exam_results';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'student_id', 'answers', 'obtained_marks', 'attended_on', 'is_active'];
}
