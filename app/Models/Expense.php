<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'exp_head_id', 'name', 'date', 'amount', 'is_active', 'created_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function expenseHead(): BelongsTo
    {
        return $this->belongsTo(ExpenseHead::class, 'exp_head_id');
    }
}
