<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransportFee extends Model
{
    protected $table = 'student_transport_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['transport_feemaster_id', 'student_session_id', 'route_pickup_point_id', 'generated_by', 'created_at'];

    public function transportFeemaster(): BelongsTo
    {
        return $this->belongsTo(TransportFeemaster::class, 'transport_feemaster_id', 'id');
    }
}
