<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRating extends Model
{
    protected $table = 'staff_rating';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'comment',
        'rate',
        'user_id',
        'role',
        'status',
        'entrydt',
    ];
}
