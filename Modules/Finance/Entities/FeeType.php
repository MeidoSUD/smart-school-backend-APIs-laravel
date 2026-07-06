<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $table = 'feetype';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['is_system', 'feecategory_id', 'type', 'code', 'is_active', 'description'];
}
