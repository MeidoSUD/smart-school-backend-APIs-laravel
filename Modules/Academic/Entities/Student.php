<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Entities\Category;
use Modules\Core\Entities\User;
use Modules\Operations\Entities\HostelRoom;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'parent_id', 'admission_no', 'roll_no', 'admission_date', 'firstname',
        'middlename', 'lastname', 'rte', 'image', 'mobileno', 'email',
        'state', 'city', 'pincode', 'religion', 'cast', 'dob', 'gender',
        'current_address', 'permanent_address', 'category_id', 'school_house_id',
        'blood_group', 'hostel_room_id', 'adhar_no', 'samagra_id',
        'bank_account_no', 'bank_name', 'ifsc_code', 'guardian_is',
        'father_name', 'father_phone', 'father_occupation',
        'mother_name', 'mother_phone', 'mother_occupation',
        'guardian_name', 'guardian_relation', 'guardian_phone',
        'guardian_occupation', 'guardian_address', 'guardian_email',
        'father_pic', 'mother_pic', 'guardian_pic', 'is_active',
        'previous_school', 'height', 'weight', 'measurement_date',
        'dis_reason', 'note', 'dis_note', 'app_key', 'parent_app_key', 'disable_at',
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

    public function hostelRoom(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id', 'id');
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->firstname} {$this->middlename} {$this->lastname}"),
        );
    }
}
