<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->has('homework_id')) {
            return [
                'homework_id' => 'required|integer|exists:homework,id',
                'message' => 'required|string',
                'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
            ];
        }

        return [
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'homework_date' => 'required|date',
            'submit_date' => 'required|date|after:homework_date',
            'description' => 'required|string|max:2000',
            'marks' => 'nullable|integer|min:0',
            'document' => 'nullable|file|max:10240',
        ];
    }
}
