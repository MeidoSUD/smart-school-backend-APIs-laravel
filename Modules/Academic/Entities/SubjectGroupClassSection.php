<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGroupClassSection extends Model
{
    protected $table = 'subject_group_class_sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class_section_id', 'subject_group_id', 'session_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function subjectGroup(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }
}
