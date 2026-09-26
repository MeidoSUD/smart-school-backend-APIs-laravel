<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyllabusMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // CI canonical field is subject_syllabus_id; accept legacy/mobile aliases.
        if (! $this->input('subject_syllabus_id')) {
            $alias = $this->input('syllabus_id')
                ?? $this->input('lesson_plan_id')
                ?? $this->input('id');
            if ($alias) {
                $this->merge(['subject_syllabus_id' => $alias]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'subject_syllabus_id' => 'required|integer|exists:subject_syllabus,id',
            'message' => 'required|string',
        ];
    }
}
