<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSession extends Model
{
    use HasFactory;

    protected $table = 'student_session';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'session_id', 'student_id', 'class_id', 'section_id',
        'section_id_old', 'class_id_old', 'route_id', 'vehicle_id',
        'route_pickup_point_id', 'hostel_room_id', 'hostel_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Session::class, 'session_id');
    }

    public function hostelRoom(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function studentFeeMasters(): HasMany
    {
        return $this->hasMany(StudentFeeMaster::class, 'student_session_id');
    }

    public function studentTransportFees(): HasMany
    {
        return $this->hasMany(StudentTransportFee::class, 'student_session_id');
    }

    public function studentFeesDiscounts(): HasMany
    {
        return $this->hasMany(StudentFeesDiscount::class, 'student_session_id');
    }

    public function attendences()
    {
        return $this->hasMany(StudentAttendence::class, 'student_session_id');
    }
}
