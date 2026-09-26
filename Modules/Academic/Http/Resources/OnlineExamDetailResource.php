<?php

declare(strict_types=1);

namespace Modules\Academic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin object */
final class OnlineExamDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $exam = (array) ($this->resource['exam'] ?? []);
        $stats = (array) ($this->resource['stats'] ?? []);
        $publishResult = (bool) ($this->resource['publishResult'] ?? false);

        // Full CI view.php exam contract (was stripped to id/title only).
        return [
            'id' => $exam['id'] ?? null,
            'title' => $exam['exam'] ?? $exam['exam_title'] ?? null,
            'exam' => $exam['exam'] ?? $exam['exam_title'] ?? null,
            'is_quiz' => (int) ($exam['is_quiz'] ?? 0),
            'is_active' => (int) ($exam['is_active'] ?? 0),
            'is_neg_marking' => (int) ($exam['is_neg_marking'] ?? 0),
            'is_marks_display' => (int) ($exam['is_marks_display'] ?? 1),
            'is_rank_generated' => (int) ($exam['is_rank_generated'] ?? 0),
            'attempt' => $exam['attempt'] ?? null,
            'exam_from' => $exam['exam_from'] ?? null,
            'exam_to' => $exam['exam_to'] ?? null,
            'auto_publish_date' => $exam['auto_publish_date'] ?? null,
            'duration' => $exam['duration'] ?? null,
            'answer_word_count' => $exam['answer_word_count'] ?? null,
            'answer_word_limit_display' => (($exam['answer_word_count'] ?? '') === '-1' || ($exam['answer_word_count'] ?? '') === -1) ? 'no_limit' : ($exam['answer_word_count'] ?? null),
            'passing_percentage' => $exam['passing_percentage'] ?? null,
            'description' => $exam['description'] ?? null,
            'is_attempted' => (int) ($this->resource['is_attempted'] ?? 0),
            'publish_result' => $publishResult ? 1 : 0,
            'rank' => (int) ($this->resource['rank'] ?? 0),
            'rank_display' => $this->resource['rankDisplay'] ?? 'awaited',
            'can_start' => (bool) ($this->resource['canStart'] ?? false),
            'start_blocked_reason' => $this->resource['startBlockedReason'] ?? null,
            'online_exam_student' => $this->resource['onlineExamStudent'] ?? null,
            'student' => $this->resource['student'] ?? null,
            'file_constraints' => $this->resource['fileConstraints'] ?? null,
            'question_opt' => $this->resource['questionOpt'] ?? null,
            'question_true_false' => $this->resource['questionTrueFalse'] ?? null,
            'questions' => OnlineExamQuestionResource::collection($this->resource['questions'] ?? [])
                ->each(fn (OnlineExamQuestionResource $r) => $r->showCorrect($publishResult)),
            ...$stats,
        ];
    }
}
