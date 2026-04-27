<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    protected $table = 'feecategory';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];
}
