<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'section',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}