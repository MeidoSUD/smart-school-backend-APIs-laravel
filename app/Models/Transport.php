<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'transport_route';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_title', 'route_code', 'no_of_vehicle', 'status'];
}

class PickupPoint extends Model
{
    protected $table = 'pickup_points';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_id', 'point_name', 'arrival_time', 'distance', 'pickup_route_id', 'drop_route_id', 'is_active'];
}

class VehicleRoute extends Model
{
    protected $table = 'vehicle_routes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_id', 'vehicle_id'];
}

class Vehicle extends Model
{
    protected $table = 'vehicles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'vehicle_no', 'vehicle_model', 'vehicle_photo', 'manufacture_year',
        'registration_number', 'chasis_number', 'max_seating_capacity',
        'driver_name', 'driver_licence', 'driver_contact', 'note',
    ];
}