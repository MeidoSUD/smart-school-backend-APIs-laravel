<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendenceType extends Model
{
    protected $table = 'attendence_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['type', 'key_value', 'is_active', 'for_qr_attendance', 'long_lang_name', 'long_name_style'];
}