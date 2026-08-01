<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDesignation extends Model
{
    protected $table = 'staff_designation';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['staff_designation_name'];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'staff_designation_id');
    }
}
