<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'sch_settings';

    protected $primaryKey = 'id';

    public $timestamps = false;

    // NOTE: Only commonly used fields listed. See SQL schema for all ~150 columns.
    protected $fillable = [
        'name',
        'biometric',
        'superadmin_restriction',
        'lang_id',
        'start_month',
        'currency',
        'currency_symbol',
        'currency_place',
        'date_format',
        'timezone',
        'app_logo',
        'is_rtl',
        'email',
        'phone',
        'address',
        'image',
        'session_id',
        'is_active',
        'languages',
        'dise_code',
        'time_format',
        'created_at',
        'updated_at',
    ];
}
