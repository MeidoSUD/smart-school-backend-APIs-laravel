<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class PickupPoint extends Model
{
    protected $table = 'pickup_points';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_id', 'point_name', 'arrival_time', 'distance', 'pickup_route_id', 'drop_route_id', 'is_active'];
}
