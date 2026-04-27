<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    protected $table = 'student_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'fee_groups_feetype_id', 'amount',
        'amount_discount', 'amount_fine', 'date', 'description', 'is_active',
    ];
}
