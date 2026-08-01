<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    protected $table = 'transport_route';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['route_title', 'no_of_vehicle', 'note', 'is_active'];
}
