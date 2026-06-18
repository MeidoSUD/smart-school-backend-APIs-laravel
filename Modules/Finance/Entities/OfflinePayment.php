<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class OfflinePayment extends Model
{
    protected $table = 'offline_fees_payments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'student_session_id',
        'student_fees_master_id',
        'fee_groups_feetype_id',
        'student_transport_fee_id',
        'payment_date',
        'bank_from',
        'bank_account_transferred',
        'reference',
        'amount',
        'submit_date',
        'approve_date',
        'attachment',
        'reply',
        'approved_by',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
