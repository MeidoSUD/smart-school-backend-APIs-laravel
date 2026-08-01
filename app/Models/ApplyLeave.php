<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplyLeave extends Model
{
    protected $table = 'student_applyleave';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'student_session_id', 'from_date', 'to_date', 'apply_date',
        'status', 'docs', 'reason', 'approve_by', 'approve_date', 'request_type',
    ];
}
