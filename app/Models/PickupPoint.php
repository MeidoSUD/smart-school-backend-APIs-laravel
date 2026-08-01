<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupPoint extends Model
{
    protected $table = 'pickup_point';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'latitude', 'longitude'];
}
