<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseHead extends Model
{
    protected $table = 'expense_head';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['expense_head', 'description', 'is_active'];
}
