<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    protected $table = 'hostel';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['hostel_name', 'type', 'address', 'note', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class, 'hostel_id', 'id');
    }
}
