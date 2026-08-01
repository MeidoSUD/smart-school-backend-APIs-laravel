<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'date', 'attendence_type', 'clock_in', 'clock_out',
        'late', 'early_leaving', 'overtime', 'total_rest_time', 'note', 'added_by',
    ];
}
