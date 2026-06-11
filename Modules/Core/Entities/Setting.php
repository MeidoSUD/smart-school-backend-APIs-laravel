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

    protected $guarded = [];
}
