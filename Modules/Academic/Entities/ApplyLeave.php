<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class ApplyLeave extends Model
{
    protected $table = 'apply_leave';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'apply_date', 'from_date', 'to_date',
        'reason', 'status', 'approve_by', 'docs',
    ];
}
