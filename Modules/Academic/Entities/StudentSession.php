<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Entities\Session;
use Modules\Finance\Entities\StudentFeeMaster;
use Modules\Finance\Entities\StudentFeesDiscount;
use Modules\Finance\Entities\StudentTransportFee;
use Modules\Operations\Entities\HostelRoom;

class StudentSession extends Model
{
    use HasFactory;

    protected $table = 'student_session';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'session_id',
        'route_id',
        'hostel_room_id',
        'transport_fee',
        'route_pickup_point_id',
        'transport_fee_month',
        'default_login',
        'dis_reason',
        'disc_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'transport_fee' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }

    public function hostelRoom(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id', 'id');
    }

    public function studentFeeMasters(): HasMany
    {
        return $this->hasMany(StudentFeeMaster::class, 'student_session_id', 'id');
    }

    public function studentTransportFees(): HasMany
    {
        return $this->hasMany(StudentTransportFee::class, 'student_session_id', 'id');
    }

    public function studentFeesDiscounts(): HasMany
    {
        return $this->hasMany(StudentFeesDiscount::class, 'student_session_id', 'id');
    }
}
