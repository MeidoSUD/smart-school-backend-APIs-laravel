<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubjectGroup extends Model
{
    protected $table = 'subject_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'session_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_group_subjects', 'subject_group_id', 'subject_id');
    }
}
