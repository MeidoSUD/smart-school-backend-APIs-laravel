<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineStudent extends Model
{
    protected $table = 'online_students';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'reference_no', 'firstname', 'lastname', 'class_section_id', 'dob', 'gender',
        'category_id', 'religion', 'cast', 'mobileno', 'email', 'address',
        'father_name', 'father_phone', 'father_occupation',
        'mother_name', 'mother_phone', 'mother_occupation',
        'guardian_is', 'guardian_name', 'guardian_relation', 'guardian_phone',
        'guardian_email', 'guardian_occupation', 'guardian_address',
        'school_house_id', 'blood_group', 'student_photo',
        'form_status', 'paid_status', 'admission_id',
        'file_birth_certificate', 'file_aadhar_card', 'file_school_leaving_certificate',
        'file_character_certificate', 'file_transfer_certificate', 'created_at',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }
}