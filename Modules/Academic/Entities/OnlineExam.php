<?php

declare(strict_types=1);

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OnlineExam extends Model
{
    use HasFactory;

    protected $table = 'online_exams';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'exam_title',
        'exam_type',
        'class_id',
        'section_id',
        'subject_id',
        'duration',
        'minimum_percentage',
        'max_attempts',
        'is_active',
    ];

    protected $casts = [
        'class_id' => 'integer',
        'section_id' => 'integer',
        'subject_id' => 'integer',
        'duration' => 'integer',
        'minimum_percentage' => 'float',
        'max_attempts' => 'integer',
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class, 'online_exam_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(OnlineExamResult::class, 'online_exam_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    public function scopeForClassSection(Builder $query, int $classId, int $sectionId): Builder
    {
        return $query->where('class_id', $classId)->where('section_id', $sectionId);
    }
}
