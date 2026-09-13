<?php

declare(strict_types=1);

namespace Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitOnlineExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'onlineexam_id' => ['required', 'integer', 'min:1'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required'],
            'answers.*.answer' => ['nullable'],
            'answers.*.select_option' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $current = $this->input('onlineexam_id');

        if ($current === null || $current === '') {
            $recovered = $this->recoverMismatchedMultipartValue('onlineexam_id');

            if ($recovered === null || $recovered === '') {
                foreach (['online_exam_id', 'exam_id', 'examId', 'id'] as $alias) {
                    $v = $this->input($alias);
                    if ($v !== null && $v !== '') {
                        $recovered = is_string($v) ? $v : (string) $v;
                        break;
                    }
                    $v2 = $this->recoverMismatchedMultipartValue($alias);
                    if ($v2 !== null && $v2 !== '') {
                        $recovered = $v2;
                        break;
                    }
                }
            }

            if ($recovered !== null && $recovered !== '') {
                $this->merge(['onlineexam_id' => trim((string) $recovered, "\"' ")]);
            }
        }

        // form-data sends arrays as JSON string: answers="[{...}]"
        $answers = $this->input('answers');
        if ($answers === null || $answers === '') {
            $answers = $this->recoverMismatchedMultipartRaw('answers');
        }
        if (is_string($answers)) {
            $decoded = json_decode(trim($answers), true);
            if (is_array($decoded)) {
                $this->merge(['answers' => $decoded]);
            }
        }
    }

    private function recoverMismatchedMultipartValue(string $key): ?string
    {
        $raw = $this->recoverMismatchedMultipartRaw($key);

        if ($raw !== null && $raw !== '') {
            return trim($raw);
        }

        $content = $this->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded) && isset($decoded[$key])) {
            return (string) $decoded[$key];
        }

        return null;
    }

    private function recoverMismatchedMultipartRaw(string $key): ?string
    {
        $content = $this->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        // Multiline-capable: captures JSON arrays spanning lines.
        if (preg_match('/name="' . preg_quote($key, '/') . '"[^\\r\\n]*\r?\n\r?\n(.*?)\r?\n--/s', $content, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/name="' . preg_quote($key, '/') . '"\s*\r?\n\r?\n([^\r\n]+)/', $content, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    public function examId(): int
    {
        return (int) $this->validated('onlineexam_id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function answers(): array
    {
        $answers = $this->validated('answers');

        if (is_string($answers)) {
            $decoded = json_decode($answers, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($answers) ? $answers : [];
    }
}
