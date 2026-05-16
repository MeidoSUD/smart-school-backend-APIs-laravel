<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $table = 'visitors_book';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'student_session_id',
        'source',
        'purpose',
        'name',
        'email',
        'contact',
        'id_proof',
        'no_of_people',
        'date',
        'in_time',
        'out_time',
        'note',
        'image',
        'meeting_with',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
