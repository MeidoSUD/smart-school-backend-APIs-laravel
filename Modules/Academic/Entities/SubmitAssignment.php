<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class SubmitAssignment extends Model
{
    protected $table = 'submit_assignment';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'homework_id',
        'student_id',
        'message',
        'docs',
        'file_name',
    ];
}
