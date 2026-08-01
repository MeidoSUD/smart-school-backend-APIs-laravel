<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['exam_type', 'name', 'point', 'mark_from', 'mark_upto', 'description', 'is_active'];
}
