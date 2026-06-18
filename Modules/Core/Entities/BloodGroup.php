<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class BloodGroup extends Model
{
    protected $table = 'blood_group';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
