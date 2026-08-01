<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeesProcessing extends Model
{
    protected $table = 'student_fees_processing';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_fees_master_id', 'fee_groups_feetype_id', 'amount',
        'date', 'payment_mode', 'note', 'created_by',
    ];
}
