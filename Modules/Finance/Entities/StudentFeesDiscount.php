<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeesDiscount extends Model
{
    protected $table = 'student_fees_discounts';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'student_session_id',
        'fees_discount_id',
        'status',
        'payment_id',
        'description',
        'is_active',
    ];

    public function feeDiscount(): BelongsTo
    {
        return $this->belongsTo(FeeDiscount::class, 'fees_discount_id');
    }
}
