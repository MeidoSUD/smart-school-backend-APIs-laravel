<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeSessionGroup extends Model
{
    protected $table = 'fee_session_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['fee_groups_id', 'session_id', 'is_active', 'created_at'];

    public function feeGroup(): BelongsTo
    {
        return $this->belongsTo(FeeGroup::class, 'fee_groups_id', 'id');
    }
}
