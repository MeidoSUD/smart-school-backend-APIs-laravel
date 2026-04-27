<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'from_mark', 'to_mark', 'grade_point', 'description', 'is_active'];
}
