<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['name', 'sesion_id', 'note', 'is_active'];
}
