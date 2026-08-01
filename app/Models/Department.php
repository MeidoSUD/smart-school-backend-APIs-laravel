<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['department_name'];

    public function staff()
    {
        return $this->hasMany(\App\Models\Staff::class, 'department_id');
    }
}
