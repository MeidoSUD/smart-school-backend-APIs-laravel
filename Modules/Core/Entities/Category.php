<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];
}
