<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'admission_no',
        'roll_no',
        'admission_date',
        'firstname',
        'middlename',
        'lastname',
        'rte',
        'image',
        'mobileno',
        'email',
        'state',
        'city',
        'pincode',
        'religion',
        'category_id',
        'gender',
        'religion_id',
        'route_id',
        'hostel_id',
        'house',
        'blood_group',
        'height',
        'weight',
        'measurement_date',
        'photo',
        'father_name',
        'father_phone',
        'father_occupation',
        'father_email',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'mother_email',
        'guardian_is',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_email',
        'guardian_address',
        'guardian_occupation',
        'dob',
        'certificates',
        'admission_no',
        'student_photo_id',
        'local_identity',
        'birth_place',
        'bank_accounts',
        'national_identification_number',
        'local_identification_number',
        'previous_school_details',
        'additional_notes',
        'document_title',
        'document',
        'document1',
        'document2',
        'document3',
        'document4',
        'document5',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'measurement_date' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

    public function studentSessions(): HasMany
    {
        return $this->hasMany(StudentSession::class, 'student_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname}");
    }
}