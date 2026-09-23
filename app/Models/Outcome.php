<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outcome extends Model
{
    use HasFactory;

    protected $table = 'outcomes';

    protected $fillable = [
        'stander_id',
        'name',
        'code',
        'level',
        'status',
        'session_id',
    ];

    public function stander()
    {
        return $this->belongsTo(Stander::class, 'stander_id');
    }
}
