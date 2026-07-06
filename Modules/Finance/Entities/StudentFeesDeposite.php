<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentFeesDeposite extends Model
{
    protected $table = 'student_fees_deposite';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_fees_master_id', 'fee_groups_feetype_id', 'student_transport_fee_id', 'amount_detail', 'is_active', 'created_at'];
}
