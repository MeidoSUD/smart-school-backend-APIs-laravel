<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'lang_id',
        'currency_id',
        'department',
        'designation',
        'qualification',
        'work_exp',
        'name',
        'surname',
        'father_name',
        'mother_name',
        'contact_no',
        'emergency_contact_no',
        'email',
        'dob',
        'marital_status',
        'date_of_joining',
        'date_of_leaving',
        'local_address',
        'permanent_address',
        'note',
        'image',
        'password',
        'gender',
        'account_title',
        'bank_account_no',
        'bank_name',
        'ifsc_code',
        'bank_branch',
        'payscale',
        'basic_salary',
        'epf_no',
        'contract_type',
        'shift',
        'location',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'resume',
        'joining_letter',
        'resignation_letter',
        'other_document_name',
        'other_document_file',
        'user_id',
        'is_active',
        'verification_code',
        'disable_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'dob' => 'date',
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'disable_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}