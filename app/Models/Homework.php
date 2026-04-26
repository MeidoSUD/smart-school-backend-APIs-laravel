<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homework extends Model
{
    protected $table = 'homework';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'homework_date', 'submission_date',
        'description', 'created_by', 'evaluated_by', 'evaluation_date', 'created_at',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}

class HomeworkEvaluation extends Model
{
    protected $table = 'homework_evaluation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['homework_id', 'student_id', 'docs', 'evaluation_date', 'remark', 'created_by'];
}

class DailyAssignment extends Model
{
    protected $table = 'daily_assignment';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'subject_group_subject_id', 'title',
        'description', 'attachment', 'date', 'evaluated_by', 'evaluation_date', 'remark',
    ];
}