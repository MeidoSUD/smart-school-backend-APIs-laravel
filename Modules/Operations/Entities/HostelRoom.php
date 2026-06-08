<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

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
}
