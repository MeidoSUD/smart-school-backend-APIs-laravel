<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPayroll extends Model
{
    protected $table = 'staff_payroll';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['staff_id', 'pay_scale', 'grade', 'is_active'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
