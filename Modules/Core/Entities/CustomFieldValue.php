<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['belong_table_id', 'custom_field_id', 'field_value'];
}
