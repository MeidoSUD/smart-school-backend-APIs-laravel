<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflinePayment extends Model
{
    protected $table = 'offline_payments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'amount', 'payment_date', 'payment_mode', 'status', 'description'];
}
