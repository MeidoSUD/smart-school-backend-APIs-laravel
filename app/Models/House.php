<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    protected $table = 'school_houses';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['house_name', 'description', 'is_active'];
}
