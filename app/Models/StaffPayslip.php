<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPayslip extends Model
{
    protected $table = 'staff_payslip';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'month', 'year', 'basic_salary', 'allowances',
        'total_allowance', 'deductions', 'total_deduction', 'tax',
        'net_salary', 'status', 'payment_date', 'payment_mode', 'note',
    ];
}
