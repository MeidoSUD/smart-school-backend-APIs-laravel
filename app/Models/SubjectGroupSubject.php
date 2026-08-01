<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGroupSubject extends Model
{
    protected $table = 'subject_group_subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['subject_group_id', 'subject_id', 'session_id'];

    public function subjectGroup(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }
}
