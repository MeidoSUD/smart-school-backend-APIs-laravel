<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homework extends Model
{
    protected $table = 'homework';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'homework_date', 'submit_date', 'submission_date',
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
