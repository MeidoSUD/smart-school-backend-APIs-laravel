<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfflinePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_session_id' => 'required|integer|exists:student_session,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string|max:50',
            'note' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ];
    }
}
