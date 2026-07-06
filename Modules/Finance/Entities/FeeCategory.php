<?php

namespace Modules\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

// FIXME: Table 'feecategory' does not exist in SQL schema
class FeeCategory extends Model
{
    protected $table = 'feecategory';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];


}
