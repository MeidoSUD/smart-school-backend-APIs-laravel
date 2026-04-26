<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'class',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}