<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class ExamGroup extends Model
{
    protected $table = 'exam_groups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'exam_type', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
