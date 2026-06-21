<?php

namespace Modules\Staff\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Entities\User;

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
        'gender',
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
    ];

    protected $hidden = [
        'password',
        'bank_account_no',
        'epf_no',
        'verification_code',
    ];

    protected $casts = [
        'dob' => 'date',
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'disable_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
