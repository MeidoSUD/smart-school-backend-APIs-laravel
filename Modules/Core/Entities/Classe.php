<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['class', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
