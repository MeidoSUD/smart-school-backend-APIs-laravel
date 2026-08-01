<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    protected $table = 'transport';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [];
}
