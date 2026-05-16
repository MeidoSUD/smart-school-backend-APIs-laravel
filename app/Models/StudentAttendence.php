<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendence extends Model
{
    protected $table = 'student_attendences';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['student_session_id', 'attendence_type_id', 'date', 'remark', 'is_active'];

    protected $casts = ['date' => 'date'];

    public function studentSession(): BelongsTo
    {
        return $this->belongsTo(StudentSession::class, 'student_session_id');
    }

    public function attendenceType(): BelongsTo
    {
        return $this->belongsTo(AttendenceType::class, 'attendence_type_id');
    }
}
