<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeMaster extends Model
{
    protected $table = 'student_fees_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'fee_master_id', 'amount', 'amount_detail', 'discount', 'fine', 'is_active'];
}
