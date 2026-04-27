<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeDiscount extends Model
{
    protected $table = 'fees_discounts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'name', 'code', 'type', 'percentage', 'amount', 'description', 'is_active'];
}
