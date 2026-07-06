<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['name', 'type', 'code', 'is_active'];
}
