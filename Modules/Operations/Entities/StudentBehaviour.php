<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentBehaviour extends Model
{
    protected $table = 'student_behaviour';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'point',
        'description',
        'title',
        'created_at',
    ];
}