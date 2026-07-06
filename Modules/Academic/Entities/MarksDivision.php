<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class MarksDivision extends Model
{
    protected $table = 'mark_divisions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'percentage_from', 'percentage_to', 'is_active'];


}
