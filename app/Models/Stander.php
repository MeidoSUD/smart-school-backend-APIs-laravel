<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stander extends Model
{
    use HasFactory;

    protected $table = 'standards';

    protected $fillable = [
        'subject_id',
        'name',
        'code',
        'status',
        'session_id',
    ];

    public function outcomes()
    {
        return $this->hasMany(Outcome::class, 'stander_id');
    }
}
