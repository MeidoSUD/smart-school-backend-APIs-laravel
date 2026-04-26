<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    protected $table = 'hostel';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['hostel_name', 'type', 'address', 'note', 'is_active'];
}

class HostelRoom extends Model
{
    protected $table = 'hostel_rooms';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['hostel_id', 'room_type_id', 'hostel_name', 'no_of_bed', 'cost_per_bed', 'is_active'];
}