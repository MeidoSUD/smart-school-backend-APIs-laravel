<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarksDivision extends Model
{
    protected $table = 'marks_division';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_type', 'division', 'percentage_from', 'percentage_to', 'remark', 'is_active'];
}

class Grade extends Model
{
    protected $table = 'grades';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'from_mark', 'to_mark', 'grade_point', 'description', 'is_active'];
}