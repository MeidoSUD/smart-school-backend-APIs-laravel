<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;

class AttendenceType extends Model
{
    protected $table = 'attendence_type';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['type', 'key_value', 'long_lang_name', 'long_name_style', 'is_active', 'for_qr_attendance'];
}
