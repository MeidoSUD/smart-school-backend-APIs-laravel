<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelRoom extends Model
{
    protected $table = 'hostel_rooms';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'hostel_id',
        'room_type_id',
        'room_no',
        'no_of_bed',
        'cost_per_bed',
        'title',
        'description',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class, 'hostel_id', 'id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id', 'id');
    }
}
