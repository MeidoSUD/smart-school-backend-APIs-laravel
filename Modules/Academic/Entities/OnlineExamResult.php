<?php

declare(strict_types=1);

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OnlineExamResult extends Model
{
    protected $table = 'online_exam_results';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'student_id', 'answers', 'obtained_marks', 'attended_on', 'is_active'];

    protected $casts = [
        'online_exam_id' => 'integer',
        'student_id' => 'integer',
        'answers' => 'array',
        'obtained_marks' => 'float',
        'attended_on' => 'immutable_datetime',
        'is_active' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
