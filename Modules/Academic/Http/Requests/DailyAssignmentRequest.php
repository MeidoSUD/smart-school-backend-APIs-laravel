<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'subject' => 'required|integer',
            'description' => 'nullable|string',
            'file' => 'nullable|file',
            'assigment_id' => 'nullable|integer'
        ];
    }
}
