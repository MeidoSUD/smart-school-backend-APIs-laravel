<?php

declare(strict_types=1);

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OnlineExamQuestion extends Model
{
    protected $table = 'online_exam_questions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['online_exam_id', 'question_id', 'optional'];

    protected $casts = [
        'online_exam_id' => 'integer',
        'question_id' => 'integer',
        'optional' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }
}
