<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineStudent extends Model
{
    protected $table = 'online_admissions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'reference_no', 'firstname', 'middlename', 'lastname', 'class_section_id', 'dob', 'gender',
        'category_id', 'religion', 'cast', 'mobileno', 'email', 'state', 'city', 'pincode',
        'current_address', 'permanent_address',
        'father_name', 'father_phone', 'father_occupation',
        'mother_name', 'mother_phone', 'mother_occupation',
        'guardian_is', 'guardian_name', 'guardian_relation', 'guardian_phone',
        'guardian_email', 'guardian_occupation', 'guardian_address',
        'school_house_id', 'blood_group', 'image',
        'form_status', 'paid_status', 'route_id', 'vehroute_id', 'hostel_room_id',
        'previous_school', 'height', 'weight', 'note', 'document',
        'admission_no', 'roll_no', 'admission_date', 'rte', 'is_enroll',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }
}
