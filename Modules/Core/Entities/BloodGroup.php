<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

// FIXME: Table 'blood_group' does not exist in SQL schema
class BloodGroup extends Model
{
    protected $table = 'blood_group';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'is_active'];


}
