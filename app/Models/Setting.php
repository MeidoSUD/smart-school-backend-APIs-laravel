<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'sch_settings';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'school_name',
        'print_header',
        'biometric',
        'admin_login',
        'superadmin_restriction',
        'lang_id',
        'start_month',
        'end_month',
        'currency',
        'currency_symbol',
        'currency_place',
        'date_format',
        'timezone',
        'logo',
        'app_logo',
        'file_size',
        'file_extension',
        'is_rtl',
        'lang',
        'created_at',
        'updated_at',
    ];
}