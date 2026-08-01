<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'subject_id', 'staff_id', 'class_id', 'section_id', 'class_section_id',
        'question_title', 'question_type', 'option_a', 'option_b', 'option_c',
        'option_d', 'correct_answer', 'explanation', 'marks', 'is_active',
    ];

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }
}
