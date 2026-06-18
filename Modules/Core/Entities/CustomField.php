<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $table = 'custom_fields';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name', 'belong_to', 'type', 'bs_column', 'validation',
        'field_values', 'show_table', 'visible_on_table', 'weight', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
