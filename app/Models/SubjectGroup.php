<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubjectGroup extends Model
{
    protected $table = 'subject_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'session_id'];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_group_subjects', 'subject_group_id', 'subject_id');
    }
}
