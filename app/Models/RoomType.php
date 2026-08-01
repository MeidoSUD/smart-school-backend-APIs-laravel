<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $table = 'room_types';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['room_type', 'description'];

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class, 'room_type_id', 'id');
    }
}
