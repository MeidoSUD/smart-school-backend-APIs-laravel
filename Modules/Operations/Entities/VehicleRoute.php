<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class VehicleRoute extends Model
{
    protected $table = 'vehicle_routes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_id', 'vehicle_id'];
}
