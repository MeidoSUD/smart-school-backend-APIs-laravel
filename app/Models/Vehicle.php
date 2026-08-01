<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
