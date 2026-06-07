<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class AttendenceType extends Model
{
    protected $table = 'attendence_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['type', 'key_value', 'is_active', 'for_qr_attendance', 'long_lang_name', 'long_name_style'];
}
