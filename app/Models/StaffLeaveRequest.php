<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeaveRequest extends Model
{
    protected $table = 'staff_leave_request';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'leave_type_id', 'date_from', 'date_to',
        'reason', 'status', 'applied_on', 'approved_by',
    ];
}
