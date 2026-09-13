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

        return [
            'id' => $exam['id'] ?? null,
            'title' => $exam['exam'] ?? $exam['exam_title'] ?? null,
            'is_quiz' => (int) ($exam['is_quiz'] ?? 0),
            'is_attempted' => (int) ($this->resource['is_attempted'] ?? 0),
            'publish_result' => $publishResult ? 1 : 0,
            'rank' => (int) ($this->resource['rank'] ?? 0),
            'student' => $this->resource['student'] ?? null,
            'questions' => OnlineExamQuestionResource::collection($this->resource['questions'] ?? [])
                ->each(fn (OnlineExamQuestionResource $r) => $r->showCorrect($publishResult)),
            ...$stats,
        ];
    }
}
