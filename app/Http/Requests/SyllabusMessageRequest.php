<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyllabusMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'syllabus_id' => 'required|integer|exists:subject_syllabus,id',
            'message' => 'required|string',
        ];
    }
}
