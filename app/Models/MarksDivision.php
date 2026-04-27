<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarksDivision extends Model
{
    protected $table = 'mark_divisions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['exam_type', 'division', 'percentage_from', 'percentage_to', 'remark', 'is_active'];
}
