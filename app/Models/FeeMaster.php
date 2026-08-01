<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeMaster extends Model
{
    protected $table = 'feemasters';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'session_id', 'feetype_id', 'class_id', 'feemaster_name',
        'description', 'amount', 'due_date', 'fine_type', 'fine_amount',
        'fine_percentage', 'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'fine_percentage' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Session::class, 'session_id');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'feetype_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Classe::class, 'class_id');
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class, 'student_fees_master_id');
    }
}
