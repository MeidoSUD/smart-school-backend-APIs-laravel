<?php

declare(strict_types=1);

namespace Modules\Academic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin object */
final class OnlineExamQuestionResource extends JsonResource
{
    private bool $showCorrect = false;

    public function showCorrect(bool $show = true): self
    {
        $this->showCorrect = $show;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'onlineexam_question_id' => $this->onlineexam_question_id ?? $this->id ?? null,
            'question_id' => $this->question_id ?? null,
            'question' => $this->question ?? null,
            'question_type' => $this->question_type ?? 'singlechoice',
            'level' => $this->level ?? null,
            'marks' => isset($this->marks) ? (float) $this->marks : 0,
            'neg_marks' => isset($this->neg_marks) ? (float) $this->neg_marks : 0,
            'options' => [
                'a' => $this->opt_a ?? null,
                'b' => $this->opt_b ?? null,
                'c' => $this->opt_c ?? null,
                'd' => $this->opt_d ?? null,
                'e' => $this->opt_e ?? null,
            ],
            'descriptive_word_limit' => $this->descriptive_word_limit ?? null,
            'subject' => [
                'name' => $this->subject_name ?? null,
                'code' => $this->subject_code ?? null,
            ],
            'select_option' => $this->select_option ?? null,
            'score_marks' => isset($this->score_marks) ? (float) $this->score_marks : 0,
            'remark' => $this->when($this->showCorrect, $this->remark ?? null),
            // Never leak the answer key unless results are published.
            'correct' => $this->when($this->showCorrect, $this->correct ?? null),
        ];
    }
}
