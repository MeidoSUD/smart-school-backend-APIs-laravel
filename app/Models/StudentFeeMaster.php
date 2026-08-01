<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeMaster extends Model
{
    protected $table = 'student_fees_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['is_system', 'student_session_id', 'fee_session_group_id', 'amount', 'is_active', 'created_at'];

    public function feeSessionGroup(): BelongsTo
    {
        return $this->belongsTo(FeeSessionGroup::class, 'fee_session_group_id', 'id');
    }
}
