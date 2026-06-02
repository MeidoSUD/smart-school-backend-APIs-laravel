<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    protected $table = 'transport_route';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['route_title', 'route_code', 'no_of_vehicle', 'status'];
}
