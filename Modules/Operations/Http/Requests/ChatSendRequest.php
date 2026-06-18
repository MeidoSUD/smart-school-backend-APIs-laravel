<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'required_without:file|string|max:5000',
            'file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,zip',
        ];
    }
}
