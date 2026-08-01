<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeGroup extends Model
{
    protected $table = 'fee_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'is_system', 'description', 'is_active', 'created_at'];
}
