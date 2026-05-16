<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGroup extends Model
{
    protected $table = 'exam_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'exam_type', 'description', 'is_active'];
}
