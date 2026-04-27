<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTransportFee extends Model
{
    protected $table = 'student_transport_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'route_id', 'pickup_point_id', 'month', 'amount', 'is_active'];
}
