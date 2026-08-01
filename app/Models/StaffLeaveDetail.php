<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeaveDetail extends Model
{
    protected $table = 'staff_leave_details';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'leave_type_id', 'date_from', 'date_to',
        'days', 'applied_on', 'reason', 'status', 'approved_by',
    ];
}
