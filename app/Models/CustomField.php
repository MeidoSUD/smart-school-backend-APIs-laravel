<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $table = 'custom_fields';
    protected $primaryKey = 'id';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name', 'belong_to', 'type', 'bs_column', 'validation',
        'field_values', 'show_table', 'visible_on_table', 'weight', 'is_active',
        'created_at', 'updated_at',
    ];


}
