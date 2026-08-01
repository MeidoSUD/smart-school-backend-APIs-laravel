<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OnlineExam extends Model
{
    protected $table = 'onlineexam';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'exam_name', 'session_id', 'exam_date', 'exam_start_time',
        'exam_end_time', 'exam_duration', 'full_marks', 'passing_marks',
        'negative_marks', 'instructions', 'is_active',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Session::class, 'session_id');
    }

    public function questions()
    {
        return $this->hasMany(OnlineExamQuestion::class, 'onlineexam_id');
    }

    public function students()
    {
        return $this->hasMany(OnlineExamStudent::class, 'onlineexam_id');
    }
}
