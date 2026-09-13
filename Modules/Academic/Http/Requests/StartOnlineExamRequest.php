<?php

declare(strict_types=1);

namespace Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StartOnlineExamRequest extends FormRequest
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
        ];
    }

    /**
     * Allow form-data to work even if client forces
     * `Content-Type: application/json` (Postman misconfiguration).
     * In that case PHP leaves $_POST empty, so recover value
     * from the raw multipart body + common aliases.
     */
    protected function prepareForValidation(): void
    {
        $current = $this->input('onlineexam_id');

        if ($current !== null && $current !== '') {
            return;
        }

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

    private function recoverMismatchedMultipartValue(string $key): ?string
    {
        $content = $this->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        // multipart/form-data raw: name="onlineexam_id"\r\n\r\n1
        if (preg_match('/name="' . preg_quote($key, '/') . '"\s*\r?\n\r?\n([^\r\n]+)/', $content, $m)) {
            return trim($m[1]);
        }

        // fallback: JSON sent as raw string but not parsed
        $decoded = json_decode($content, true);
        if (is_array($decoded) && isset($decoded[$key])) {
            return (string) $decoded[$key];
        }

        return null;
    }

    public function examId(): int
    {
        return (int) $this->validated('onlineexam_id');
    }
}
