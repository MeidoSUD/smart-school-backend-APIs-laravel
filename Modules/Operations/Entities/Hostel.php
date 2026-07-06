<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    protected $table = 'hostel';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['hostel_name', 'type', 'address', 'intake', 'description', 'is_active'];

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class, 'hostel_id', 'id');
    }
}
