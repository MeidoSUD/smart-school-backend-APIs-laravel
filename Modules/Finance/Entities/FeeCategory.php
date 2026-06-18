<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    protected $table = 'feecategory';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
