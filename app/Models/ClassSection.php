<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    use HasFactory;

    protected $table = 'class_sections';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'section_id',
        'is_active',
        'created_at',
        'updated_at',
    ];


}
