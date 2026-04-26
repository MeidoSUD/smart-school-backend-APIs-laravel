<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    protected $table = 'feecategory';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['category', 'is_active'];
}

class FeeType extends Model
{
    protected $table = 'feetype';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['is_system', 'feecategory_id', 'type', 'code', 'is_active', 'description'];
}

class FeeMaster extends Model
{
    protected $table = 'feemasters';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'feetype_id', 'class_id', 'amount', 'description', 'is_active'];
}

class StudentFee extends Model
{
    protected $table = 'student_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_session_id', 'fee_groups_feetype_id', 'amount',
        'amount_discount', 'amount_fine', 'date', 'description', 'is_active',
    ];
}

class StudentFeeMaster extends Model
{
    protected $table = 'student_fee_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'fee_master_id', 'amount', 'amount_detail', 'discount', 'fine', 'is_active'];
}

class StudentTransportFee extends Model
{
    protected $table = 'student_transport_fees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'route_id', 'pickup_point_id', 'month', 'amount', 'is_active'];
}

class FeeDiscount extends Model
{
    protected $table = 'fees_discounts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['session_id', 'name', 'code', 'type', 'percentage', 'amount', 'description', 'is_active'];
}