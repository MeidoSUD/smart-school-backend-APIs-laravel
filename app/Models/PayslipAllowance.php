<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipAllowance extends Model
{
    protected $table = 'payslip_allowance';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['staff_id', 'staff_payslip_id', 'payscale_allowance_id', 'amount', 'is_active'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function payslip()
    {
        return $this->belongsTo(StaffPayslip::class, 'staff_payslip_id');
    }
}
