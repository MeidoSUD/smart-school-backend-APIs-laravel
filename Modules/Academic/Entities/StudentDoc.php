<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class StudentDoc extends Model
{
    protected $table = 'student_doc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'title',
        'doc',
    ];
}