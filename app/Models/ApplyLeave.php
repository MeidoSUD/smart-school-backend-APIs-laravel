<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplyLeave extends Model
{
    use HasFactory;

    protected $table = 'student_applyleave';

    public $timestamps = false;

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

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'apply_date' => 'date',
        'approve_date' => 'date',
    ];
}
