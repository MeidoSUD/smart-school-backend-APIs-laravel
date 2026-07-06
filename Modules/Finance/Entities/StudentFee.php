<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    protected $table = 'student_fees';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'student_session_id', 'feemaster_id', 'amount',
        'amount_discount', 'amount_fine', 'description', 'date', 'payment_mode', 'is_active',
    ];
}
