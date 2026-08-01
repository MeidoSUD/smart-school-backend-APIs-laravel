<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    protected $table = 'staff_roles';
    protected $fillable = ['role_id', 'staff_id', 'is_active'];
}
