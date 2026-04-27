<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeMaster extends Model
{
    protected $table = 'feemasters';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'feetype_id', 'class_id', 'amount', 'description', 'is_active'];
}
