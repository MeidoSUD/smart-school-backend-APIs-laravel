<?php

namespace Modules\Operations\Entities;

use Illuminate\Database\Eloquent\Model;

class BehaviourSetting extends Model
{
    protected $table = 'behaviour_settings';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'comment_option',
        'created_at',
    ];
}