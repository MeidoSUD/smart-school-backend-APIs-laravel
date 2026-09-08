<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class ApplyLeave extends Model
{
    protected $table = 'student_applyleave';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'student_session_id',
        'from_date',
        'to_date',
        'apply_date',
        'status',
        'docs',
        'reason',
        'approve_by',
        'approve_date',
        'request_type',
    ];

    public function studentSession()
    {
        return $this->belongsTo(StudentSession::class, 'student_session_id');
    }

    public function staff()
    {
        return $this->belongsTo(\Modules\Staff\Entities\Staff::class, 'approve_by');
    }
}
