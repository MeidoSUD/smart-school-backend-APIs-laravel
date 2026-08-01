<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $table = 'income';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'income_head_id', 'name', 'date', 'amount', 'is_active', 'created_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function incomeHead(): BelongsTo
    {
        return $this->belongsTo(IncomeHead::class, 'income_head_id');
    }
}
