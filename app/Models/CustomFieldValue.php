<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';
    protected $primaryKey = 'id';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = ['belong_table_id', 'custom_field_id', 'field_value'];
}
