<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsConfig extends Model
{
    protected $table = 'sms_config';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'sms_config_name', 'sms_service', 'api_id', 'api_key',
        'auth_token', 'senderid', 'contact', 'is_active',
    ];

    public function isActive(): bool
    {
        return $this->is_active === 'yes';
    }
}
