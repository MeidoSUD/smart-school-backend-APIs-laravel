<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'admission_no', 'admission_date', 'student_photo', 'roll_no',
        'First_name', 'Last_name', 'Father_name', 'Father_phone', 'Father_occupation',
        'Mother_name', 'Mother_phone', 'Mother_occupation',
        'Guardian_name', 'Guardian_phone', 'Guardian_occupation', 'Guardian_relation',
        'Guardian_address', 'student_email', 'student_phone', 'student_gender',
        'dob', 'category_id', 'school_house_id', 'blood_group', 'religion',
        'if_guardian_is', 'is_bank_detail', 'bank_name', 'bank_account_no',
        'bank_code', 'bank_branch', 'student_address', 'is_active', 'is_login',
        'parent_id', 'user_id', 'disable_reason', 'student_status',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function studentSessions(): HasMany
    {
        return $this->hasMany(StudentSession::class, 'student_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function schoolHouse(): BelongsTo
    {
        return $this->belongsTo(House::class, 'school_house_id');
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->First_name} {$this->Last_name}"),
        );
    }
}
