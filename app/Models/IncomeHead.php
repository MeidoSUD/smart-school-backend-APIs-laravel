<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeHead extends Model
{
    protected $table = 'income_head';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['income_head', 'description', 'is_active'];
}
