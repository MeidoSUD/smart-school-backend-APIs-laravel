<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    protected $table = 'homework';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'class_id', 'section_id', 'session_id', 'staff_id', 'subject_group_subject_id',
        'subject_id', 'homework_date', 'submission_date', 'submit_date', 'marks',
        'description', 'create_date', 'evaluation_date', 'document', 'created_by',
        'evaluated_by', 'created_at',
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

    public function submitAssignments(): HasMany
    {
        return $this->hasMany(SubmitAssignment::class, 'homework_id');
    }
}
