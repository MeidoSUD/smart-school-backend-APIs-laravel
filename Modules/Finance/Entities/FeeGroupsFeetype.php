<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeGroupsFeetype extends Model
{
    protected $table = 'fee_groups_feetype';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'fee_session_group_id', 'fee_groups_id', 'feetype_id', 'session_id',
        'amount', 'fine_type', 'due_date', 'fine_percentage', 'fine_amount', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected $dates = ['due_date'];

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'feetype_id', 'id');
    }
}
