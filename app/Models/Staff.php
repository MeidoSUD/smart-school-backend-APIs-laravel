<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'staff_id', 'employee_id', 'role_id', 'staff_designation_id', 'department_id',
        'staff_name', 'fathers_name', 'mothers_name', 'date_of_birth', 'cnic_no',
        'marital_status', 'phone', 'email', 'address', 'gender', 'qualification',
        'work_exp', 'note', 'date_of_joining', 'date_of_leaving', 'employee_salary',
        'is_active', 'is_login',
    ];

    protected $hidden = ['password'];
    protected $casts = ['date_of_birth' => 'date', 'date_of_joining' => 'date', 'date_of_leaving' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'staff_roles', 'staff_id', 'role_id')
            ->withPivot('is_active');
    }

    public function attendance()
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    public function leaveDetails()
    {
        return $this->hasMany(StaffLeaveDetail::class, 'staff_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(StaffLeaveRequest::class, 'staff_id');
    }

    public function payslips()
    {
        return $this->hasMany(StaffPayslip::class, 'staff_id');
    }

    public function payroll(): HasOne
    {
        return $this->hasOne(StaffPayroll::class, 'staff_id');
    }

    public function timeline()
    {
        return $this->hasMany(StaffTimeline::class, 'staff_id');
    }

    public function ratings()
    {
        return $this->hasMany(StaffRating::class, 'staff_id');
    }
}
