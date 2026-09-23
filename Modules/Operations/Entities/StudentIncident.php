<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentIncident extends Model
{
    protected $table = 'student_incidents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'student_id',
        'incident_id',
        'assign_by',
        'created_at',
    ];

    public function behaviour(): BelongsTo
    {
        return $this->belongsTo(StudentBehaviour::class, 'incident_id', 'id');
    }
}