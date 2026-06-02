<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Staff\Entities\Staff;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'type', 'code', 'teacher_id', 'is_active'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }
}
