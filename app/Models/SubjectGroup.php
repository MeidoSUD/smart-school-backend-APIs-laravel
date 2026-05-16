<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectGroup extends Model
{
    protected $table = 'subject_group';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['group_name', 'is_active'];
}
