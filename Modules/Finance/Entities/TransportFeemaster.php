<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class TransportFeemaster extends Model
{
    protected $table = 'transport_feemaster';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'month', 'due_date', 'fine_amount', 'fine_type', 'fine_percentage'];

    protected $casts = [
        'fine_amount' => 'float',
        'fine_percentage' => 'float',
    ];

    protected $dates = ['due_date'];
}
